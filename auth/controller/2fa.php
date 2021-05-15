<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

use RobThree\Auth\TwoFactorAuth;

/**
 * Render the account page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/auth/2fa', function (
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

  //setup the 2fa instance
  $auth = new TwoFactorAuth($config->get('settings', 'name') ?? 'Incept');
  //if there isn't a secret
  if (!isset($data['item']['qr_secret'])) {
    //create one
    $data['item']['qr_secret'] = $auth->createSecret();
  }

  //determine the account name
  $name = 'User';
  if ($request->getSession('me', 'auth_email')
    && $request->getSession('me', 'auth_email_verified')
  ) {
    $name = $request->getSession('me', 'auth_email');
  } else if ($request->getSession('me', 'auth_phone')
    && $request->getSession('me', 'auth_phone_verified')
  ) {
    $name = $request->getSession('me', 'auth_phone');
  } else if ($request->getSession('me', 'auth_username')) {
    $name = $request->getSession('me', 'auth_username');
  }
  //generate the QR code
  $data['item']['qr_code'] = $auth->getQRCodeImageAsDataUri(
    $name,
    $data['item']['qr_secret']
  );

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
    ->renderFromFolder('2fa', $data);

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
$this('http')->post('/auth/2fa', function (
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
  $route = $request->getStage('route') ?? '/auth/2fa';

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

  //----------------------------//
  // 4. Prepare Data
  $data = $request->getStage();
  //setup the 2fa instance
  $auth = new TwoFactorAuth($config->get('settings', 'name') ?? 'Incept');

  //----------------------------//
  // 5. Validate Data
  if (!isset($data['qr_secret'])) {
    $response->setError(true, 'Missing key');
    return $http->routeTo('get', $route, $request, $response);
  }

  if (!isset($data['qr_code'])) {
    $response->invalidate('qr_code', 'Code is required');
  } else if (!$auth->verifyCode($data['qr_secret'], $data['qr_code'])){
    $response->invalidate('qr_code', 'Code is invalid');
  }

  if (!$response->isValid()) {
    $response->setError(true, 'Invalid parameters');
    return $http->routeTo('get', $route, $request, $response);
  }

  //----------------------------//
  // 5. Process Request
  //trigger the job
  $emitter->call('system-object-auth-update', [
    'auth_id' => $request->getSession('me', 'auth_id'),
    'auth_2fa_key' => $data['qr_secret']
  ], $response);

  //----------------------------//
  // 7. Interpret Results
  if ($response->isError()) {
    return $http->routeTo('get', $route, $request, $response);
  }

  //.... it was good ....

  //add a flash
  $message = $lang->translate('Two factor is set');
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

/**
 * Render the sign in page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/auth/signin/2fa', function (
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
  $redirect = $config->get('settings', 'home') ?? '/';
  if ($request->getStage('redirect_uri')) {
    $redirect = $request->getStage('redirect_uri');
  }

  //----------------------------//
  // 3. Security Check
  // already logged in?
  if ($request->getSession('me')) {
    return $http->redirect($redirect);
  }

  //missing 2fa in session?
  if (!$request->getSession('2fa')) {
    //add a flash
    $response->setSession('flash', [
      'message' => $lang->translate('Missing 2FA Configuration'),
      'type' => 'error'
    ]);
    return $http->redirect($redirect);
  }

  //----------------------------//
  // 4. Prepare Data
  //Prepare body
  $data = ['item' => $request->getPost()];

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
    ->renderFromFolder('signin2fa', $data);

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
$this('http')->post('/auth/signin/2fa', function (
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
  $route = $request->getStage('route') ?? '/auth/signin/2fa';

  //determine redirect
  $redirect = $config->get('settings', 'home') ?? '/';
  if ($request->getStage('redirect_uri')) {
    $redirect = $request->getStage('redirect_uri');
  }

  //----------------------------//
  // 3. Security Checks
  //----------------------------//
  // 4. Prepare Data
  //get the form data
  $data = $request->getStage();
  //setup the 2fa instance
  $auth = new TwoFactorAuth($config->get('settings', 'name') ?? 'Incept');
  $session = $request->getSession('2fa');

  //----------------------------//
  // 5. Validate Data
  if (!isset($data['qr_code'])) {
    $response->invalidate('qr_code', 'Code is required');
  } else if (!$auth->verifyCode($session['auth_2fa_key'], $data['qr_code'])){
    $response->invalidate('qr_code', 'Code is invalid');
  }

  //if there are errors
  if (!$response->isValid()) {
    $response->setError(true, 'Invalid Parameters');
    return $http->routeTo('get', $route, $request, $response);
  }

  //----------------------------//
  // 6. Process Request
  $response->removeSession('2fa')->setSession('me', $session);

  //----------------------------//
  // 7. Interpret Results
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
