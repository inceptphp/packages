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
 * Render the forgot page
 *
 * FORGOT FLOW:
 * - GET /forgot
 * - POST /forgot
 * - EMAIL
 * - GET /recover/auth_id/hash
 * - POST /recover/auth_id/hash
 * - GET /login
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/auth/forgot', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Declare Packages
  $auth = $this('auth');
  $lang = $this('lang');
  $config = $this('config');
  $emitter = $this('event');
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
  $data = [ 'item' => $request->getPost() ];

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
    ->renderFromFolder('forgot', $data);

  //if we only want the body
  if ($request->getStage('render') === 'body') {
    return;
  }

  //set content
  $response
    ->set('page', 'title', $lang->translate('Forgot Password'))
    ->set('page', 'class', 'page-auth-forgot page-auth')
    ->setContent($body);

  //render page
  $emitter->emit('render-blank', $request, $response);
});

/**
 * Process the Forgot Page
 *
 * FORGOT FLOW:
 * - GET /forgot
 * - POST /forgot
 * - EMAIL
 * - GET /recover/auth_id/hash
 * - POST /recover/auth_id/hash
 * - GET /login
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/auth/forgot', function (
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
  $route = $request->getStage('route') ??'/auth/forgot';

  //determine redirect
  $redirect = $request->getStage('redirect_uri') ?? '/auth/forgot';

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
      return $this->routeTo('get', $route, $request, $response);
    }
  } else if (count($attempts) > $authConfig['captcha']) {
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
  // 5. Process Request
  //trigger the job
  $emitter->emit('auth-detail', $request, $response);
  if ($response->isError()) {
    //add attempt
    $auth->addAttempt($request);
    return $http->routeTo('get', $route, $request, $response);
  }

  //----------------------------//
  // 6. Interpret Results
  //its good
  //determine the type
  if ($response->getResults('auth_email')
    && $response->getResults('auth_email_verified')
  ) {
    $type = 'email';
  } else if ($response->getResults('auth_phone')
    && $response->getResults('auth_phone_verified')
  ) {
    $type = 'phone';
  } else {
    //add attempt
    $auth->addAttempt($request);
    //set the error
    $message = $lang->translate('Your account is not activated.');
    $response->setError(true, $message);
    //route back
    return $http->routeTo('get', $route, $request, $response);
  }

  //send the verification
  $emitter->call($type . '-otp-send', [
    'to' => $response->getResults('auth_' . $type)
  ], $response);
  if ($response->isError()) {
    return $http->routeTo('get', $route, $request, $response);
  }

  //clear submit attempts
  $auth->clearAttempts($request);
  //set the otp in session
  $otp = $response->getResults('otp');
  if ($otp) {
    $response->setSession('otp', $otp);
  }

  //if we dont want to redirect
  if ($redirect === 'false') {
    return;
  }

  $http->redirect(sprintf(
    '/auth/forgot/%s/otp/%s/%s',
    $type,
    $response->getResults('auth_id'),
    md5($response->getResults('auth_updated'))
  ));
});

/**
 * Render the OTP Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/auth/forgot/:type/otp/:auth_id/:hash', function(
  RequestInterface $request,
  ResponseInterface $response
) {
  //same logic as verify
  $this('http')->routeTo('get', sprintf(
    '/auth/verify/%s/otp/%s/%s',
    $request->getStage('type'),
    $request->getStage('auth_id'),
    $request->getStage('hash')
  ), $request, $response);
});

/**
 * Process the OTP Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/auth/forgot/:type/otp/:auth_id/:hash', function(
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Type Check
  $type = $request->getStage('type');
  //if no valid type
  if ($type !== 'email' && $type !== 'phone') {
    //let it 404
    return;
  }

  //----------------------------//
  // 2. Declare Packages
  $lang = $this('lang');
  $http = $this('http');
  $emitter = $this('event');

  //----------------------------//
  // 3. Setup Overrides
  $hash = $request->getStage('hash');
  $authId = $request->getStage('auth_id');
  //determine route
  $route = $request->getStage('route') ?? sprintf(
    '/auth/forgot/%s/otp/%s/%s',
    $type,
    $authId,
    $hash
  );
  //determine redirect
  $redirect = $request->getStage('redirect_uri') ?? '/auth/signin';

  //----------------------------//
  // 4. Prepare Data
  $code = $request->getStage('otp');

  //----------------------------//
  // 5. Validate Data
  //if invalid ID
  if (!is_numeric($authId)) {
    //let it 404
    return;
  }

  $auth = $emitter->call('system-object-auth-detail', $request);
  //if there's an error or hash is invalid
  if (!isset($auth['auth_' . $type]) || $hash !== md5($auth['auth_updated'])) {
    //let it 404
    return;
  }

  //csrf check
  $emitter->emit('csrf-validate', $request, $response);
  if ($response->isError()) {
    return $http->routeTo('get', $route, $request, $response);
  }

  //if code does not match
  if ($code != $request->getSession('otp')) {
    $response->setError(true, $lang->translate('Invalide code'));
    $response->setError(true, $lang->translate($code . ' != ' . $request->getSession('otp')));
    return $http->routeTo('get', $route, $request, $response);
  }

  //----------------------------//
  // 6. Process Request
  //it was good, if we dont want to redirect
  if ($redirect === 'false') {
    return;
  }

  //redirect
  $http->redirect(sprintf(
    '/auth/forgot/recover/%s/%s/%s',
    $auth['auth_id'],
    $hash,
    $code
  ));
});

/**
 * Render the Recover Page
 *
 * FORGOT FLOW:
 * - GET /forgot
 * - POST /forgot
 * - EMAIL
 * - GET /recover/auth_id/hash
 * - POST /recover/auth_id/hash
 * - GET /login
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/auth/forgot/recover/:auth_id/:hash/:otp', function (
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
  $code = $request->getStage('otp');
  $hash = $request->getStage('hash');
  $authId = $request->getStage('auth_id');

  //----------------------------//
  // 5. Validate Data
  //if the auth id is invalid or if code does not match
  if (!is_numeric($authId) || $code != $request->getSession('otp')) {
    //let it 404
    return;
  }

  $auth = $emitter->call('system-object-auth-detail', $request);
  //if there's an error or hash is invalid
  if (!$auth || $hash !== md5($auth['auth_updated'])) {
    //let it 404
    return;
  }

  //Prepare body
  $data = ['item' => $request->getPost()];

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
  // 6. Render Template
  $template = dirname(__DIR__) . '/template';
  if (is_dir($response->get('page', 'template_root'))) {
    $template = $response->get('page', 'template_root');
  }

  $body = $handlebars
    ->setTemplateFolder($template)
    ->renderFromFolder('recover', $data);

  //if we only want the body
  if ($request->getStage('render') === 'body') {
    return;
  }

  //set content
  $response
    ->set('page', 'title', $lang->translate('Sign In'))
    ->set('page', 'class', 'page-auth-recover page-auth')
    ->setContent($body);

  //render page
  $emitter->emit('render-blank', $request, $response);
});

/**
 * Process the Recover Page
 *
 * FORGOT FLOW:
 * - GET /forgot
 * - POST /forgot
 * - EMAIL
 * - GET /recover/auth_id/hash
 * - POST /recover/auth_id/hash
 * - GET /login
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/auth/forgot/recover/:auth_id/:hash/:otp', function (
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
  // 2. Prepare Data
  $code = $request->getStage('otp');
  $hash = $request->getStage('hash');
  $authId = $request->getStage('auth_id');

  //----------------------------//
  // 3. Setup Overrides
  //determine route
  $route = $request->getStage('route') ?? sprintf(
    '/auth/forgot/recover/%s/%s/%s',
    $authId,
    $hash,
    $code
  );

  //determine redirect
  $redirect = $request->getStage('redirect_uri')
    ?? $config->get('settings', 'home')
    ?? '/';

  //----------------------------//
  // 4. Validate Data
  //if the auth id is invalid or if code does not match
  if (!is_numeric($authId) || $code != $request->getSession('otp')) {
    //let it 404
    return;
  }

  $auth = $emitter->call('system-object-auth-detail', $request);
  //if there's an error or hash is invalid
  if (!$auth || $hash !== md5($auth['auth_updated'])) {
    //let it 404
    return;
  }

  //csrf check
  $emitter->emit('csrf-validate', $request, $response);
  if ($response->isError()) {
    return $http->routeTo('get', $route, $request, $response);
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
  // 5. Process Request
  //trigger the job
  $emitter->call('system-object-auth-update', [
    'auth_id' => $authId,
    'auth_password' => $request->getStage('auth_password')
  ], $response);

  //----------------------------//
  // 7. Interpret Results
  if ($response->isError()) {
    return $http->routeTo('get', $route, $request, $response);
  }

  //.... it was good ....

  //remove otp
  $response->removeSession('otp');

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
