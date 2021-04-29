<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Incept\Framework\Schema;

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

/* Sign In/Up/Out Routes
-------------------------------- */

/**
 * Render the sign up page
 *
 * SIGNUP FLOW:
 * - GET /signup
 * - POST /signup
 * - EMAIL
 * - GET /activate/auth_id/hash
 * - GET /signin
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/auth/signup/:type', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Declare Packages
  $lang = $this('lang');
  $emitter = $this('event');
  $config = $this('config');
  $handlebars = $this('handlebars');

  //----------------------------//
  // 2. Setup Overrides
  //determine redirect
  $redirect = $request->getStage('redirect_uri')
    ?? $config->get('settings', 'home')
    ?? '/';

  //----------------------------//
  // 3. Security Check
  // already logged in?
  if ($request->getSession('me')) {
    return $http->redirect($redirect);
  }

  //----------------------------//
  // 4. Prepare Data
  //Prepare body
  $data = [
    'type' => $request->getStage('type'),
    'item' => $request->getPost()
  ];

  $authSchema = Schema::load('auth');
  $data['auth_schema'] = $authSchema->get();

  $profileSchema = Schema::load('profile');
  $data['profile_schema'] = $profileSchema->get();

  //add CSRF
  $emitter->emit('csrf-load', $request, $response);
  $data['csrf'] = $response->getResults('csrf');

  //add captcha
  $emitter->emit('captcha-load', $request, $response);
  $data['captcha'] = $response->getResults('captcha');

  if ($response->isError()) {
    //add a flash
    $response->setSession('flash', [
      'message' => $response->getMessage(),
      'type' => 'error'
    ]);
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
    ->renderFromFolder('signup', $data);

  //if we only want the body
  if ($request->getStage('render') === 'body') {
    return;
  }

  //set content
  $response
    ->set('page', 'title', $lang->translate('Sign Up'))
    ->set('page', 'class', 'page-auth-signup page-auth')
    ->setContent($body);

  //render page
  $emitter->emit('render-blank', $request, $response);
});

/**
 * Process the Signup Page
 *
 * SIGNUP FLOW:
 * - GET /signup
 * - POST /signup
 * - EMAIL
 * - GET /activate/auth_id/hash
 * - GET /login
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/auth/signup/:type', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Declare Packages
  $auth = $this('auth');
  $lang = $this('lang');
  $http = $this('http');
  $emitter = $this('event');

  //----------------------------//
  // 2. Setup Overrides
  //determine route
  $type = $request->getStage('type');
  //determine route
  $route = $request->getStage('route') ?? sprintf('/auth/signup/%s', $type);
  //determine next
  $next = $request->getStage('next') ?? sprintf('/auth/verify');
  //determine redirect
  $redirect = $request->getStage('redirect_uri') ?? '/auth/signin';

  //----------------------------//
  // 3. Security Checks
  $attempts = $auth->getAttempts($request);
  $authConfig = $auth->config();

  if (count($attempts) >= $authConfig['lockout']) {
    //add attempt
    $auth->addAttempt($request);
    $wait = $auth->waitFor($request);

    if ($wait) {
      $message = $lang->translate(sprintf(
        'Too many submission attempts please wait %s minutes before trying again.',
        number_format(ceil($wait / 60))
      ));

      $response->setError(true, $message);
      return $http->routeTo('get', $route, $request, $response);
    }
  }

  //csrf check
  $csrf = $request->getSession('csrf');
  $emitter->emit('csrf-validate', $request, $response);
  if ($response->isError()) {
    //add attempt
    $auth->addAttempt($request);
    return $http->routeTo('get', $route, $request, $response);
  }

  //captcha check
  $emitter->emit('captcha-validate', $request, $response);
  if ($response->isError()) {
    //add attempt
    $auth->addAttempt($request);
    return $http->routeTo('get', $route, $request, $response);
  }

  //----------------------------//
  // 4. Prepare Data
  $data = $request->getStage();

  //----------------------------//
  // 5. Validate Data
  //let the event validate
  //----------------------------//
  // 6. Process Request
  //trigger the job
  $emitter->emit('auth-create', $request, $response);

  //----------------------------//
  // 7. Interpret Results
  if ($response->isError()) {
    //add attempt
    $auth->addAttempt($request);
    return $http->routeTo('get', $route, $request, $response);
  }

  //its good
  $auth->clearAttempts($request);

  //if we dont want to redirect
  if ($redirect === 'false') {
    return;
  }

  $request->setSoftStage($response->getResults());

  // send verify
  if ($type === 'email' || $type === 'phone') {
    $request
      ->setStage('route', $next)
      ->copy('stage.auth_' . $type, 'stage.auth_slug');

    $response->setSession('csrf', $csrf);
    return $http->routeTo('post', $next, $request, $response);
  }

  //add a flash
  $response->setSession('flash', [
    'message' => $lang->translate('Sign Up Successful.'),
    'type' => 'success'
  ]);

  $http->redirect($redirect);
});
