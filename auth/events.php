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

/**
 * Creates a auth
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('auth-create', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Declare Packages
  $lang = $this('lang');
  $emitter = $this('event');

  //----------------------------//
  // 1. Get Data
  $data = $request->getStage() ?? [];

  if (!isset($data['auth_active'])) {
    $request->setStage('auth_active', 0);
  }

  //----------------------------//
  // 2. Validate Data
  $auth = Schema::load('auth');
  $profile = Schema::load('profile');

  $errors = array_merge(
    $auth->getErrors($data),
    $profile->getErrors($data)
  );

  //auth will require profile
  unset($errors['profile_id']);

  //if there are errors
  if (!empty($errors)) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->invalidate($errors);
  }

  //----------------------------//
  // 3. Process Data
  // check profile
  if (!isset($data['profile_id'])) {
    // trigger model create
    $emitter->emit('system-object-profile-create', $request, $response);

    if ($response->isError()) {
      return;
    }

    // set profile id
    $request->setStage('profile_id', $response->getResults());
    //reset the json
    $response->remove('json');
  }

  //trigger model create
  $emitter->emit('system-object-auth-create', $request, $response);

  //remove password, confirm
  $response->removeResults('auth_password');
});

/**
 * Creates a auth
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('auth-detail', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //set profile as schema
  $request->setStage('schema', 'auth');

  //trigger model detail
  $this->trigger('system-model-detail', $request, $response);
});

/**
 * Auth Forgot Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('auth-forgot', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Get Data
  $this->trigger('auth-detail', $request, $response);

  if ($response->isError()) {
    return;
  }

  $data = [];
  if ($request->hasStage()) {
    $data = $request->getStage();
  }

  //----------------------------//
  // 3. Validate Data
  //validate
  $errors = AuthValidator::getForgotErrors($data);

  //if there are errors
  if (!empty($errors)) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->set('json', 'validation', $errors);
  }

  //----------------------------//
  // 4. Process Data
  //send mail
  $request->setSoftStage($response->getResults());

  //because there's no way the CLI queue would know the host
  $protocol = 'http';
  if ($request->getServer('SERVER_PORT') === 443) {
    $protocol = 'https';
  }

  $request->setStage('host', $protocol . '://' . $request->getServer('HTTP_HOST'));
  $data = $request->getStage();

  $queuePackage = $this->package('cradlephp/cradle-queue');
  if (!$queuePackage->queue('auth-forgot-mail', $data)) {
    //send mail manually after the connection
    $this->postprocess(function (
  RequestInterface $request,
  ResponseInterface $response
) {
      $this->trigger('auth-forgot-mail', $request, $response);
    });
  }

  //return response format
  $response
    ->setError(false)
    ->removeResults('auth_password')
    ->removeResults('confirm');
});

/**
 * Auth Forgot Mail Job (supporting job)
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('auth-forgot-mail', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $config = $this->package('global')->service('mail-main');

  if (!$config) {
    return;
  }

  //if it's not configured
  if ($config['user'] === '<EMAIL ADDRESS>'
    || $config['pass'] === '<EMAIL PASSWORD>'
  ) {
    return;
  }

  //form hash
  $authId = $request->getStage('auth_id');
  $authUpdated = $request->getStage('auth_updated');
  $hash = md5($authId.$authUpdated);

  //form link
  $host = $request->getStage('host');
  $link = $host . '/auth/recover/' . $authId . '/' . $hash;

  //prepare data
  $from = [];
  $from[$config['user']] = $config['name'];

  $to = [];
  $to[$request->getStage('auth_slug')] = null;

  $subject = $this->package('global')->translate('Password Recovery from Cradle!');

  if ($request->getStage('subject')) {
    $subject = $this->package('global')->translate($request->getStage('subject'));
  }

  $handlebars = $this->package('global')->handlebars();

  $templateRoot = __DIR__ . '/template/email';
  if ($request->hasStage('template_root')
    && is_dir($request->getStage('template_root'))
  ) {
    $templateRoot = $request->getStage('template_root');
  }

  $contents = file_get_contents($templateRoot . '/recover.txt');
  $template = $handlebars->compile($contents);
  $text = $template(['host' => $host, 'link' => $link]);

  $contents = file_get_contents($templateRoot . '/recover.html');
  $template = $handlebars->compile($contents);
  $html = $template(['host' => $host, 'link' => $link]);

  //send mail
  $message = new Swift_Message($subject);
  $message->setFrom($from);
  $message->setTo($to);
  $message->setBody($html, 'text/html');
  $message->addPart($text, 'text/plain');

  $transport = Swift_SmtpTransport::newInstance();
  $transport->setHost($config['host']);
  $transport->setPort($config['port']);
  $transport->setEncryption($config['type']);
  $transport->setUsername($config['user']);
  $transport->setPassword($config['pass']);

  $swift = Swift_Mailer::newInstance($transport);
  $swift->send($message, $failures);
});

/**
 * Auth Login Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('auth-login', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Get Data
  $data = [];
  if ($request->hasStage()) {
    $data = $request->getStage();
  }

  //----------------------------//
  // 2. Validate Data
  $errors = AuthValidator::getLoginErrors($data);

  //if there are errors
  if (!empty($errors)) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->set('json', 'validation', $errors);
  }

  //----------------------------//
  // 3. Process Data
  $this->trigger('auth-detail', $request, $response);

  //remove password, confirm
  $response
    ->removeResults('auth_password')
    ->removeResults('confirm');
});

/**
 * Auth Recover Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('auth-recover', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Get Data
  $data = [];
  if ($request->hasStage()) {
    $data = $request->getStage();
  }

  //----------------------------//
  // 2. Validate Data
  $errors = AuthValidator::getRecoverErrors($data);

  //if there are errors
  if (!empty($errors)) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->set('json', 'validation', $errors);
  }

  //----------------------------//
  // 3. Process Data
  //update
  $this->trigger('auth-update', $request, $response);

  //return response format
  $response->setError(false);

  //remove password, confirm
  $response
    ->removeResults('auth_password')
    ->removeResults('confirm');
});

/**
 * Removes a auth
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('auth-remove', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  // set auth as schema
  $request->setStage('schema', 'auth');
  // trigger model create
  $this->trigger('system-model-remove', $request, $response);
});

/**
 * Restores a auth
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('auth-restore', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  // set auth as schema
  $request->setStage('schema', 'auth');
  // trigger model create
  $this->trigger('system-model-restore', $request, $response);
});

/**
 * Searches auth
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('auth-search', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //set auth as schema
  $request->setStage('schema', 'auth');

  //trigger model search
  $this->trigger('system-model-search', $request, $response);
});

/**
 * Auth SSO Login Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('auth-sso-login', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //get data
  $data = [];
  if ($request->hasStage()) {
    $data = $request->getStage();
  }

  //load up the detail
  $this->trigger('auth-detail', $request, $response);
  if ($request->getStage('profile') && !$response->isError()) {
    return $response->setError(true);
  }

  //if there's an error
  if ($response->isError()) {
    //they don't exist
    $auth = $this->method('auth-create', $request);
    $response->setResults($auth);
  }

  $response->setError(false)->remove('json', 'message');

  // if auth is not active yet, update
  if (!$response->getResults('auth_active')) {
    $request->setStage('auth_active', 1);
    $request->setStage('auth_id', $response->getResults('auth_id'));
    $this->trigger('auth-update', $request, $response);
  }

  //load up the detail
  $this->trigger('auth-detail', $request, $response);
});

/**
 * Updates a auth
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('auth-update', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //set auth as schema
  $request->setStage('schema', 'auth');

  //trigger model search
  $this->trigger('system-model-update', $request, $response);

  //remove password, confirm
  $response
    ->removeResults('auth_password')
    ->removeResults('confirm');
});

/**
 * Auth Verify Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('auth-verify', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Get Data
  $data = [];
  if ($request->hasStage()) {
    $data = $request->getStage();
  }

  //----------------------------//
  // 2. Validate Data
  $errors = AuthValidator::getVerifyErrors($data);

  //if there are errors
  if (!empty($errors)) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->set('json', 'validation', $errors);
  }

  //----------------------------//
  // 3. Prepare Data
  //get the auth detail
  $this->trigger('auth-detail', $request, $response);

  //if there's an error
  if ($response->isError()) {
    return;
  }

  //send mail
  $request->setSoftStage($response->getResults());

  //because there's no way the CLI queue would know the host
  $protocol = 'http';
  if ($request->getServer('SERVER_PORT') === 443) {
    $protocol = 'https';
  }

  $request->setStage('host', $protocol . '://' . $request->getServer('HTTP_HOST'));
  $data = $request->getStage();

  //----------------------------//
  // 3. Process Data
  //try to queue, and if not
  $queuePackage = $this->package('cradlephp/cradle-queue');
  if (!$queuePackage->queue('auth-verify-mail', $data)) {
    //send mail manually after the connection
    $this->postprocess(function (
  RequestInterface $request,
  ResponseInterface $response
) {
      $this->trigger('auth-verify-mail', $request, $response);
    });
  }

  //return response format
  $response
    ->setError(false)
    ->removeResults('auth_password')
    ->removeResults('confirm');
});

/**
 * Auth Verify Mail Job (supporting job)
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('auth-verify-mail', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $config = $this->package('global')->service('mail-main');

  if (!$config) {
    return;
  }

  //if it's not configured
  if ($config['user'] === '<EMAIL ADDRESS>'
    || $config['pass'] === '<EMAIL PASSWORD>'
  ) {
    return;
  }

  //form hash
  $authId = $request->getStage('auth_id');
  $authUpdated = $request->getStage('auth_updated');
  $hash = md5($authId.$authUpdated);

  //form link
  $host = $request->getStage('host');
  $link = $host . '/auth/activate/' . $authId . '/' . $hash;

  //prepare data
  $from = [];
  $from[$config['user']] = $config['name'];

  $to = [];
  $to[$request->getStage('auth_slug')] = null;

  $subject = $this->package('global')->translate('Account Verification from Cradle!');

  if ($request->getStage('subject')) {
    $subject = $this->package('global')->translate($request->getStage('subject'));
  }

  $handlebars = $this->package('global')->handlebars();

  $templateRoot = __DIR__ . '/template/email';
  if ($request->hasStage('template_root')
    && is_dir($request->getStage('template_root'))
  ) {
    $templateRoot = $request->getStage('template_root');
  }

  $contents = file_get_contents($templateRoot . '/verify.txt');
  $template = $handlebars->compile($contents);
  $text = $template(['host' => $host, 'link' => $link]);

  $contents = file_get_contents($templateRoot . '/verify.html');
  $template = $handlebars->compile($contents);
  $html = $template(['host' => $host, 'link' => $link]);

  //send mail
  $message = new Swift_Message($subject);
  $message->setFrom($from);
  $message->setTo($to);
  $message->setBody($html, 'text/html');
  $message->addPart($text, 'text/plain');

  $transport = Swift_SmtpTransport::newInstance();
  $transport->setHost($config['host']);
  $transport->setPort($config['port']);
  $transport->setEncryption($config['type']);
  $transport->setUsername($config['user']);
  $transport->setPassword($config['pass']);

  $swift = Swift_Mailer::newInstance($transport);
  $swift->send($message, $failures);
});
