<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

/**
 * Render the Request Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/dialog/request', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Declare Packages
  $http = $this('http');
  $lang = $this('lang');
  $emitter = $this('event');
  $handlebars = $this('handlebars');

  //----------------------------//
  // 2. Prepare Data
  //for logged in
  if (!$request->hasSession('me', 'auth_id')) {
    return $http->redirect(sprintf(
    '/auth/signin?redirect_uri=%s',
    urlencode($_SERVER['REQUEST_URI'])
    ));
  }

  if ($response->isError()) {
    $response->setSession('flash', [
      'message' => $response->getMessage(),
      'type' => 'error'
    ]);
    $data['errors'] = $response->getValidation();
  }

  //validate parameters
  if (!$request->hasStage('client_id') || !$request->hasStage('redirect_uri')) {
    return $http->routeTo('get', '/dialog/invalid', $request, $response);
  }

  //get app detail
  $token = $request->getStage('client_id');
  $request->setStage('app_token', $token);
  $emitter->emit('system-object-app-detail', $request, $response);

  //get the app
  $app = $response->getResults();

  if (!$app || empty($app)) {
    return $http->routeTo('get', '/dialog/invalid', $request, $response);
  }

  $permitted = $app['scope'];

  $requested = [];
  if ($request->hasStage('scope')) {
    $requested = explode(',', $request->getStage('scope'));
  }

  //the final permission set
  $permissions = [];
  foreach ($app['scope'] as $scope) {
    //if this is not a user scope
    if ($scope['scope_type'] !== 'user') {
      continue;
    }

    //if this scope is being requested for
    if (!in_array($scope['scope_slug'], $requested)) {
      continue;
    }

    $permissions[$scope['scope_slug']] = $scope;
  }

  //Prepare body
  $data = [
    'permissions' => $permissions,
    'app' => $app
  ];

  //if we only want the raw data
  if ($request->getStage('render') === 'false') {
    return $response->setResults($data);
  }

  //add CSRF
  $emitter->emit('csrf-load', $request, $response);
  $data['csrf'] = $response->getResults('csrf');

  //----------------------------//
  // 3. Render Template
  $template = __DIR__ . '/template';
  if (is_dir($response->get('page', 'template_root') ?? '')) {
    $template = $response->get('page', 'template_root');
  }

  $body = $handlebars
    ->setTemplateFolder($template)
    ->renderFromFolder('request', $data);

  //set content
  $response
    ->set('page', 'title', $lang->translate('Request Access'))
    ->set('page', 'class', 'page-dialog-request page-dialog')
    ->setContent($body);

  //if we only want the body
  if ($request->getStage('render') === 'body') {
    return;
  }

  //render page
  $emitter->emit('render-blank', $request, $response);
});

/**
 * Process the Request Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/dialog/request', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Declare Packages
  $http = $this('http');
  $lang = $this('lang');
  $emitter = $this('event');
  $handlebars = $this('handlebars');

  //----------------------------//
  // 2. Prepare Data
  //for logged in
  if (!$request->hasSession('me', 'auth_id')) {
    return $http->redirect(sprintf(
      '/auth/signin?redirect_uri=%s',
      urlencode($_SERVER['REQUEST_URI'])
    ));
  }

  //csrf check
  $emitter->emit('csrf-validate', $request, $response);
  if ($response->isError()) {
    return $http->routeTo('get', '/dialog/invalid', $request, $response);
  }

  //validate parameters
  if (!$request->hasStage('client_id') || !$request->hasStage('redirect_uri')) {
    return $http->routeTo('get', '/dialog/invalid', $request, $response);
  }

  if ($request->getStage('action') !== 'allow') {
    //redirect
    $url = $request->getStage('redirect_uri');
    return $http->redirect($url . '?error=deny');
  }

  //get the profile
  $profile = $request->getSession('me');

  //get the app
  $token = $request->getStage('client_id');
  $request->setStage('app_token', $token);
  $emitter->emit('system-object-app-detail', $request, $response);
  $app = $response->getResults();

  //next we need to get the permissions from the form submission
  $permissions = $request->getStage('permissions');
  if (!is_array($permissions)) {
    $permissions = [];
  }

  //loop through the scopes
  foreach ($app['scope'] as $scope) {
    //if this is not a user scope
    if ($scope['scope_type'] === 'user') {
      continue;
    }

    //even if it wasn't requested for, let's just give the access
    $permissions[] = $scope['scope_id'];
  }

  //then stuff the permission back into stage
  $request
    ->setStage('scope_id', $permissions)
    ->setStage('session_key', uniqid())
    ->setStage('session_secret', uniqid());

  //now call the create job
  $request->setStage('profile_id', $profile['profile_id']);
  $request->setStage('app_id', $app['app_id']);

  $emitter->emit('system-object-session-create', $request, $response);
  if ($response->isError()) {
    return $http->routeTo('get', '/dialog/invalid', $request, $response);
  }

  $session = $emitter->call('system-object-session-detail', [
    'session_id' => $response->getResults()
  ]);

  if (!$session) {
    return $http->routeTo('get', '/dialog/invalid', $request, $response);
  }

  //it was good

  //redirect
  $url = $request->getStage('redirect_uri');
  $http->redirect($url . '?code=' . $session['session_token']);
});

/**
 * Render the Invalid Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/dialog/invalid', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //prepare data
  $data = [];
  if ($response->exists('json')) {
    $data = $response->get('json');
  }

  //----------------------------//
  // 3. Render Template
  $template = __DIR__ . '/template';
  if (is_dir($response->get('page', 'template_root') ?? '')) {
    $template = $response->get('page', 'template_root');
  }

  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->renderFromFolder('invalid');

  //set content
  $response
    ->set('page', 'title', $this('lang')->translate('Invalid Request'))
    ->set('page', 'class', 'page-dialog-invalid page-dialog')
    ->setContent($body);

  //if we only want the body
  if ($request->getStage('render') === 'body') {
    return;
  }

  //render page
  $this('event')->emit('render-blank', $request, $response);
});
