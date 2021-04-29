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
 * Render the Verify Page
 *
 * VERIFY FLOW:
 * - GET /verify
 * - POST /verify
 * - EMAIL
 * - GET /activate/auth_id/hash
 * - GET /login
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/auth/verify', function (
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
    ->renderFromFolder('verify', $data);

  //if we only want the body
  if ($request->getStage('render') === 'body') {
    return;
  }

  //set content
  $response
    ->set('page', 'title', $lang->translate('Verify Account'))
    ->set('page', 'class', 'page-auth-verify page-auth')
    ->setContent($body);

  //render page
  $emitter->emit('render-blank', $request, $response);
});

/**
 * Render the Verify Page
 *
 * VERIFY FLOW:
 * - GET /verify
 * - POST /verify
 * - EMAIL
 * - GET /activate/auth_id/hash
 * - GET /login
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/auth/verify', function (
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
  $route = $request->getStage('route') ??'/auth/verify';

  //determine redirect
  $redirect = $request->getStage('redirect_uri') ?? '/auth/verify';

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
  //just check if it exists (ignore active)
  $request->setStage('check', 1);

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
  if ($request->getStage('auth_slug') === $response->getResults('auth_email')) {
    $type = 'email';
  } else if ($request->getStage('auth_slug') === $response->getResults('auth_phone')) {
    $type = 'phone';
  } else {
    //add attempt
    $auth->addAttempt($request);
    //set the error
    $message = $lang->translate('No account found');
    $response->setError(true, $message);
    //route back
    return $http->routeTo('get', $route, $request, $response);
  }

  //if it's already verified
  if ($response->getResults('auth_' . $type . '_verified')) {
    //clear submit attempts
    $auth->clearAttempts($request);
    //set the error
    $message = $lang->translate('Account is already verified');
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
    '/auth/verify/%s/otp/%s/%s',
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
$this('http')->get('/auth/verify/:type/otp/:auth_id/:hash', function(
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
  $config = $this('config');
  $emitter = $this('event');
  $handlebars = $this('handlebars');

  //----------------------------//
  // 3. Setup Overrides
  //determine redirect
  $redirect = $request->getStage('redirect_uri')
    ?? $config->get('settings', 'home')
    ?? '/';

  //----------------------------//
  // 4. Security Check
  // already logged in?
  if ($request->getSession('me')) {
    return $http->redirect($redirect);
  }

  //----------------------------//
  // 5. Prepare Data
  $data = $request->getStage();

  //add CSRF
  $emitter->emit('csrf-load', $request, $response);
  $data['csrf'] = $response->getResults('csrf');

  //----------------------------//
  // 6. Validate Data
  //if invalid ID
  if (!is_numeric($data['auth_id'])) {
    //let it 404
    return;
  }

  $auth = $emitter->call('system-object-auth-detail', $request);
  //if there's an error or hash is invalid
  if (!isset($auth['auth_' . $type]) || $data['hash'] !== md5($auth['auth_updated'])) {
    //let it 404
    return;
  }

  if ($response->isError()) {
    //add a flash
    $response->setSession(
      'flash', [
      'message' => $response->getMessage(),
      'type' => 'error'
    ]);

    $data['errors'] = $response->getValidation();
  }

  //----------------------------//
  // 7. Render Template
  $template = $response->get('page', 'template_root')
    ?? dirname(__DIR__) . '/template';

  $body = $handlebars
    ->setTemplateFolder($template)
    ->renderFromFolder('otp', $data);

  //if we only want the body
  if ($request->getStage('render') === 'body') {
    return;
  }

  //set content
  $response
    ->set('page', 'title', $lang->translate('Enter OTP'))
    ->set('page', 'class', 'page-auth page-otp')
    ->setContent($body);

  //render page
  $emitter->emit('render-blank', $request, $response);
});

/**
 * Process the OTP Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/auth/verify/:type/otp/:auth_id/:hash', function(
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
  // 1. Declare Packages
  $lang = $this('lang');
  $http = $this('http');
  $emitter = $this('event');

  //----------------------------//
  // 2. Setup Overrides
  $hash = $request->getStage('hash');
  $authId = $request->getStage('auth_id');
  //determine route
  $route = $request->getStage('route') ?? sprintf(
    '/auth/verify/%s/otp/%s/%s',
    $type,
    $authId,
    $hash
  );
  //determine redirect
  $redirect = $request->getStage('redirect_uri') ?? '/auth/signin';

  //----------------------------//
  // 3. Prepare Data
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
    return $http->routeTo('get', $route, $request, $response);
  }

  //----------------------------//
  // 6. Process Request
  //remove otp
  $response->removeSession('otp');
  //trigger the job
  $emitter->call('system-object-auth-update', [
    'auth_id' => $authId,
    'auth_' . $type . '_verified' => 1,
    'auth_active' => 1
  ], $response);

  //----------------------------//
  // 7. Interpret Results
  if ($response->isError()) {
    return $http->routeTo('get', $route, $request, $response);
  }

  //it was good

  //update session
  if ($request->hasSession('me')) {
    $request->setSession('me', 'auth_' . $type . '_verified', 1);
  }

  //add a flash
  $message = $lang->translate('Activation Successful! Please Sign In.');
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
