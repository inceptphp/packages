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
 * Render the sign in page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/auth/signin', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Declare Packages
  $auth = $this('auth');
  $http = $this('http');
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
  $data = ['item' => $request->getPost()];

  $attempts = $auth->getAttempts($request);
  $authConfig = $auth->config();

  if (count($attempts) >= $authConfig['captcha']) {
    //add Captcha
    $emitter->emit('captcha-load', $request, $response);
    $data['captcha'] = $response->getResults('captcha');
  }

  //add CSRF
  $emitter->emit('csrf-load', $request, $response);
  $data['csrf'] = $response->getResults('csrf');

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
    ->renderFromFolder('signin', $data);

  //if we only want the body
  if ($request->getStage('render') === 'body') {
    return;
  }

  //set content
  $response
    ->set('page', 'title', $lang->translate('Sign In'))
    ->set('page', 'class', 'page-auth-signin page-auth')
    ->setContent($body);

  //render page
  $emitter->emit('render-blank', $request, $response);
});

/**
 * Process the Login Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/auth/signin', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Declare Packages
  $auth = $this('auth');
  $lang = $this('lang');
  $http = $this('http');
  $config = $this('config');
  $emitter = $this('event');

  //----------------------------//
  // 2. Setup Overrides
  //determine route
  $route = $request->getStage('route') ?? '/auth/signin';

  //determine redirect
  $redirect = $request->getStage('redirect_uri')
    ?? $config->get('settings', 'home')
    ?? '/';

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
  } else if (count($attempts) >= $authConfig['captcha']) {
    //captcha check
    $emitter->emit('captcha-validate', $request, $response);

    if ($response->isError()) {
      //add attempt
      $auth->addAttempt($request);
      return $http->routeTo('get', $route, $request, $response);
    }
  }

  //csrf check
  $emitter->emit('csrf-validate', $request, $response);
  if ($response->isError()) {
    //add attempt
    $auth->addAttempt($request);
    return $http->routeTo('get', $route, $request, $response);
  }

  //----------------------------//
  // 4. Prepare Data
  //----------------------------//
  // 5. Validate Data
  if (!$request->getStage('auth_slug')) {
    $response->invalidate('auth_slug', 'Cannot be empty');
  }

  if (!$request->getStage('auth_password')) {
    $response->invalidate('auth_password', 'Password is required');
  }

  //if there are errors
  if (!$response->isValid()) {
    //add attempt
    $auth->addAttempt($request);
    $response->setError(true, 'Invalid Parameters');
    return $http->routeTo('get', $route, $request, $response);
  }

  //----------------------------//
  // 6. Process Request
  //call the job
  $emitter->emit('auth-detail', $request, $response);
  if ($response->isError()) {
    //add attempt
    $auth->addAttempt($request);
    return $http->routeTo('get', $route, $request, $response);
  }

  // if account is not activated
  if (!$response->getResults('auth_active')) {
    //add attempt
    $auth->addAttempt($request);
    //set the error
    $message = $lang->translate('Your account is not activated.');
    $response->setError(true, $message);
    //route back
    return $http->routeTo('get', $route, $request, $response);
  }

  //if the passwords don't match
  if (!password_verify(
    $request->getStage('auth_password'),
    $response->getResults('auth_password')
  )) {
    //add attempt
    $auth->addAttempt($request);
    //set the error
    $message = $lang->translate('Invalid Parameters');
    $response
      ->setError(true, $message)
      ->invalidate('auth_password', 'Password incorrect');
    //route back
    return $http->routeTo('get', $route, $request, $response);
  }

  //----------------------------//
  // 7. Interpret Results
  //it was good
  $auth->clearAttempts($request);

  if ($response->getResults('auth_2fa_key')) {
    //store to session
    $response->setSession('2fa', $response->getResults());
    //go to the redirect page
    return $http->redirect(sprintf(
      '/auth/signin/2fa?%s',
      http_build_query($request->getGet())
    ));
  }

  //store to session
  $response->setSession('me', $response->getResults());

  //if we dont want to redirect
  if ($redirect === 'false') {
    return;
  }

  //add a flash
  $response->setSession('flash', [
    'message' => $lang->translate('Welcome!'),
    'type' => 'success'
  ]);

  $http->redirect($redirect);
});
