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
 * Render the account page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/auth/password', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Declare Packages
  $lang = $this('lang');
  $http = $this('http');
  $config = $this('config');
  $emitter = $this('event');
  $handlebars = $this('handlebars');

  //----------------------------//
  // 2. Setup Overrides
  //determine redirect
  $redirect = $config->get('settings', 'home') ?? '/';
  if ($request->getStage('redirect_uri')) {
    $redirect = $request->getStage('redirect_uri');
  }

  //----------------------------//
  // 3. Security Checks
  //Need to be logged in
  if (!$request->hasSession('me')) {
    //add a flash
    $response->setSession('flash', [
      'message' => $lang->translate('Please sign in first'),
      'type' => 'error'
    ]);

    $http->redirect($redirect);
  }

  //----------------------------//
  // 4. Prepare Data
  //Prepare body
  $data = [ 'item' => $request->getPost() ];

  //add CSRF
  $emitter->emit('csrf-load', $request, $response);
  $data['csrf'] = $response->getResults('csrf');

  //If no post
  if (!$request->hasPost('profile_name')) {
    //set default data
    $data['item'] = $request->getSession('me');
  }

  if ($response->isError()) {
    $data['errors'] = $response->getValidation();
  }

  //----------------------------//
  // 5. Render Template
  $template = dirname(__DIR__) . '/template';
  if (is_dir($response->get('page', 'template_root'))) {
    $template = $response->get('page', 'template_root');
  }

  $body = $handlebars
    ->setTemplateFolder($template)
    ->renderFromFolder('password', $data);

  //if we only want the body
  if ($request->getStage('render') === 'body') {
    return;
  }

  //set content
  $response
    ->set('page', 'title', $lang->translate('My Account'))
    ->set('page', 'class', 'page-auth-account page-auth')
    ->setContent($body);

  //render page
  $emitter->emit('render-blank', $request, $response);
});

/**
 * Process the Account Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/auth/password', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Declare Packages
  $lang = $this('lang');
  $http = $this('http');
  $config = $this('config');
  $emitter = $this('event');

  //----------------------------//
  // 2. Setup Overrides
  //determine route
  $route = $request->getStage('route') ?? '/auth/password';

  //determine redirect
  $redirect = $config->get('settings', 'home') ?? '/';
  if ($request->getStage('redirect_uri')) {
    $redirect = $request->getStage('redirect_uri');
  }

  //----------------------------//
  // 3. Security Checks
  //need to be online
  if (!$request->hasSession('me')) {
    //add a flash
    $response->setSession('flash', [
      'message' => $lang->translate('Please sign in first'),
      'type' => 'error'
    ]);

    $http->redirect($redirect);
  }

  //csrf check
  $emitter->emit('csrf-validate', $request, $response);

  if ($response->isError()) {
    return $http->routeTo('get', $route, $request, $response);
  }

  //----------------------------//
  // 4. Prepare Data
  $data = $request->getStage();

  //----------------------------//
  // 5. Validate Data
  if (!$request->getStage('current_password')) {
    $response->invalidate('current_password', 'Current password is required');
  }

  if (!$request->getStage('auth_password')) {
    $response->invalidate('auth_password', 'New password is required');
  }

  if ($response->isValid()) {
    $auth = $emitter->call('system-object-auth-detail', [
      'auth_id' => $request->getSession('me', 'auth_id')
    ]);

    if (!$auth) {
      $response->setError(true, 'Account not found');
      //route back
      return $http->routeTo('get', $route, $request, $response);
    }

    //if the passwords don't match
    if (!password_verify(
      $request->getStage('current_password'),
      $auth['auth_password']
    )) {
      $response->invalidate('current_password', 'Password incorrect');
    }
  }

  if (!$response->isValid()) {
    $response->setError(true, 'Invalid parameters');
    //route back
    return $http->routeTo('get', $route, $request, $response);
  }

  //----------------------------//
  // 5. Process Request
  //trigger the job
  $emitter->call('system-object-auth-update', [
    'auth_id' => $request->getSession('me', 'auth_id'),
    'auth_password' => $request->getStage('auth_password')
  ], $response);

  //----------------------------//
  // 7. Interpret Results
  if ($response->isError()) {
    return $http->routeTo('get', $route, $request, $response);
  }

  //.... it was good ....

  //add a flash
  $message = $lang->translate('Password changed');
  $response->setSession('flash', [
    'message' => $message,
    'type' => 'success'
  ]);

  //if we dont want to redirect
  if ($redirect === 'false') {
    return;
  }

  //redirect
  $http->redirect($redirect);
});
