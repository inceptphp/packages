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
 * Render the OTP Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/sms/otp/send', function(
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Declare Packages
  $http = $this('http');
  $lang = $this('lang');
  $emitter = $this('event');

  //----------------------------//
  // 2. Setup Overrides
  //determine redirect
  $redirect = $request->getStage('redirect_uri') ?? '/sms/otp';

  //----------------------------//
  // 3. Prepare Data
  $data = $request->getStage();

  //----------------------------//
  // 4. Validate Data
  if (!isset($data['to'])) {
    //add a flash
    $response->setSession('flash', [
      'message' => $lang->translate('Invalid recipient'),
      'type' => 'error'
    ]);

    return $http->redirect($redirect);
  }

  //----------------------------//
  // 5. Process Request
  $emitter->emit('sms-otp-send', $request, $response);

  if ($response->isError()) {
    //add a flash
    $response->setSession('flash', [
      'message' => $response->getMessage(),
      'type' => 'error'
    ]);

    return $http->redirect($redirect);
  }

  $otp = $response->getResults('otp');
  if ($otp) {
    $response->setSession('otp', $otp);
  }
});

/**
 * Render the OTP Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/sms/otp', function(
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
  // 2. Setup Overrides
  //determine redirect
  $redirect = $request->getStage('redirect_uri')
    ?? $config->get('settings', 'home')
    ?? '/';

  //----------------------------//
  // 3. Prepare Data
  $data = $request->getStage();

  //add CSRF
  $emitter->emit('csrf-load', $request, $response);
  $data['csrf'] = $response->getResults('csrf');

  //----------------------------//
  // 4. Validate Data
  if (!isset($data['recipient'])) {
    //add a flash
    $response->setSession('flash', [
      'message' => $lang->translate('Invalid recipient'),
      'type' => 'error'
    ]);

    return $http->redirect($redirect);
  }

  //----------------------------//
  // 4. Render Template
  $template = $response->get('page', 'template_root') ?? __DIR__ . '/template';

  $body = $handlebars
    ->setTemplateFolder($template)
    ->renderFromFolder('otp');

  //if we only want the body
  if ($request->getStage('render') === 'body') {
    return;
  }

  //set content
  $response
    ->set('page', 'title', $lang->translate('Enter OTP'))
    ->set('page', 'class', 'page-otp')
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
$this('http')->post('/sms/otp', function(
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Declare Packages
  $lang = $this('lang');
  $http = $this('http');
  $emitter = $this('event');

  //----------------------------//
  // 2. Setup Overrides
  //determine route
  $route = $request->getStage('route') ?? '/sms/otp';
  //determine route
  $redirect = $request->getStage('redirect_uri')
    ?? $config->get('settings', 'home')
    ?? '/';

  //----------------------------//
  // 3. Prepare Data
  $code = $request->getStage('otp');

  //----------------------------//
  // 4. Process Request
  //csrf check
  $emitter->emit('csrf-validate', $request, $response);

  if ($response->isError()) {
    return $http->routeTo('get', $route, $request, $response);
  }

  //if code does not match
  if ($code != $request->getSession('otp')) {
    $message = $lang->translate('Invalide code');
    $response->setError(true, $message);
    return $http->routeTo('get', $route, $request, $response);
  }

  //remove otp
  $response->removeSession('otp');
  //set error false
  $response->setError(false);
});
