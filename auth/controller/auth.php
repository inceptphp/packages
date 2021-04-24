<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Incept\Framework\Schema;
use UGComponents\OAuth\OAuth2;

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
  $handlebars = $this('handlebars');

  //----------------------------//
  // 2. Prepare Data
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
    $response->setFlash($response->getMessage(), 'error');
    $data['errors'] = $response->getValidation();
  }

  //----------------------------//
  // 3. Render Template
  $template = dirname(__DIR__) . '/template';
  if (is_dir($response->getPage('template_root'))) {
    $template = $response->getPage('template_root');
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
  $route = $request->getStage('route') ?? '/auth/signup';

  //determine redirect
  $redirect = $request->getStage('redirect_uri') ?? '/auth/login';

  //----------------------------//
  // 2. Security Checks
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
  // 3. Prepare Data
  $data = $request->getStage();

  //----------------------------//
  // 4. Validate Data
  $authSchema = Schema::load('auth');
  $profileSchema = Schema::load('profile');

  $errors = array_merge(
    $authSchema->getErrors($data),
    $profileSchema->getErrors($data)
  );

  //auth will require profile
  unset($errors['profile_id']);

  //if there are errors
  if (!empty($errors)) {
    $response
      ->setError(true, 'Invalid Parameters')
      ->invalidate($errors);

    return $http->routeTo('get', $route, $request, $response);
  }

  //----------------------------//
  // 5. Process Request
  //trigger the job
  $emitter->emit('auth-create', $request, $response);

  //----------------------------//
  // 6. Interpret Results
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

  // send verify email
  $emitter->emit('auth-verify', $request, $response);

  //add a flash
  $response->setSession('flash', [
    'message' => $this('lang')->translate(
      'Sign Up Successful. Please check your email for verification process.'
    ),
    'type' => 'success'
  ]);

  $http->redirect($redirect);
});

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
  // 2. Prepare Data
  // get home page
  $home = $config->get('settings', 'home') ?? '/';

  // already logged in?
  if ($request->getSession('me')) {
    return $http->redirect($home);
  }

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
    $response->setFlash($response->getMessage(), 'error');
    $data['errors'] = $response->getValidation();
  }

  //----------------------------//
  // 3. Render Template
  $template = dirname(__DIR__) . '/template';
  if (is_dir($response->getPage('template_root'))) {
    $template = $response->getPage('template_root');
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
 * Process an OAuth2 Login
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/auth/sso/signin/oauth2/:name', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Declare Packages
  $lang = $this('lang');
  $host = $this('host');
  $http = $this('http');
  $config = $this('config');
  $emitter = $this('event');

  //----------------------------//
  // 2. Prepare Overrides
  //determine route
  $route = $request->getStage('route') ?? '/auth/account';

  //determine redirect
  $redirect = $request->getStage('redirect_uri')
    ?? $config->get('settings', 'home')
    ?? '/';

  //----------------------------//
  // 2. Prepare Data
  $name = $request->getStage('name');
  // get config
  $oauth = $config->get('services', 'oauth2-' . $name);

  if (!$oauth
    || !isset($oauth['client_id'])
    || !isset($oauth['client_secret'])
    || !isset($oauth['url_authorize'])
    || !isset($oauth['url_access_token'])
    || !isset($oauth['url_resource'])
    || !$oauth['active']
  ) {
    //add a flash
    $response->setSession('flash', [
      'message' => $lang->translate('Invalid Service. Try again'),
      'type' => 'error'
    ]);

    return $http->redirect($redirect);
  }

  //get provider
  $provider = new OAuth2(
    // The client ID assigned to you by the provider
    $oauth['client_id'],
    // The client password assigned to you by the provider
    $oauth['client_secret'],
    // http://www.example.com/some/page.html?foo=bar
    $host->url(),
    $oauth['url_authorize'],
    $oauth['url_access_token'],
    $oauth['url_resource']
  );

  //if there is not a code
  if (!$request->hasStage('code')) {
    //we need to know where to go
    $request->setSession('redirect_uri', $redirect);

    if (isset($oauth['scope'])) {
      //set scope
      $scope = $oauth['scope'];
      if (!is_array($oauth['scope'])){
        $scope = [ $oauth['scope'] ];

      }

      $provider->setScope(...$scope);
    }

    //get sign in url
    $loginUrl = $provider->getLoginUrl();
    //redirect
    return $http->redirect($loginUrl);
  }

  //there's a code
  try {
    $accessToken = $provider->getAccessTokens($request->getStage('code'));
  } catch (Throwable $e) {
    // When Graph returns an error
    //add a flash
    $response->setSession('flash', [
      'message' => $e->getMessage(),
      'type' => 'error'
    ]);

    return $http->redirect($redirect);
  }

  if (isset($accessToken['error']) && $accessToken['error']) {
    //add a flash
    $response->setSession('flash', [
      'message' => $lang->translate('Access Token Error'),
      'type' => 'error'
    ]);
    return $http->redirect($redirect);
  }

  if (!isset($accessToken['access_token'])
    || !isset($accessToken['access_secret'])
  ) {
    //add a flash
    $response->setSession('flash', [
      'message' => $lang->translate('Access Token Error'),
      'type' => 'error'
    ]);
    return $http->redirect($redirect);
  }

  $token = $accessToken['access_token'];
  $secret = $accessToken['access_secret'];

  //Now you can get user info
  //access token from $token
  try {
    $user = $provider->get([ 'access_token' => $token ]);
  } catch (Throwable $e) {
    //add a flash
    $response->setSession('flash', [
      'message' => $e->getMessage(),
      'type' => 'error'
    ]);
    return $http->redirect($redirect);
  }

  if (isset($user['error']) && $user['error']) {
    //add a flash
    $response->setSession('flash', [
      'message' => $lang->translate('Resource Request Error'),
      'type' => 'error'
    ]);
    return $http->redirect($redirect);
  }

  //set some defaults
  $request->setStage('profile_email', $user['email']);
  $request->setStage('profile_name', $user['name']);
  $request->setStage('auth_slug', $user['email']);
  $request->setStage('auth_password', $user['id']);
  $request->setStage('auth_active', 1);
  $request->setStage('confirm', $user['id']);
  //there might be more information
  $request->setStage('resource', $user);

  $emitter->emit('auth-sso-login', $request, $response);

  if ($response->isError()) {
    //add a flash
    $response->setSession('flash', [
      'message' => $response->getMessage(),
      'type' => 'error'
    ]);
    return $http->redirect($redirect);
  }

  //it was good
  //store to session
  $response->setSession('me', $response->getResults());
  $response->setSession('me', 'access_token', $token);
  $response->setSession('me', 'access_secret', $secret);

  //if we dont want to redirect
  if ($redirect === 'false') {
    return;
  }

  return $http->redirect($redirect);
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
  $route = $request->getStage('route') ?? '/auth/login';

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
  // 5. Process Request
  //call the job
  $emitter->emit('auth-login', $request, $response);

  //----------------------------//
  // 6. Interpret Results
  if ($response->isError()) {
    //add attempt
    $auth->addAttempt($request);
    return $http->routeTo('get', $route, $request, $response);
  }

  // if account is not activated
  if ($response->getResults('auth_active') == 0) {
    //add attempt
    $auth->addAttempt($request);
    //add a flash
    $response->setSession('flash', [
      'message' => $lang->translate('Your account is not activated.'),
      'type' => 'warning'
    ]);
    // set redirect
    return $http->redirect('/auth/signin');
  }

  //it was good
  $package->clearAttempts($request);
  //store to session
  //TODO: Sessions for clusters
  $request->setSession('me', $response->getResults());

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

/**
 * Process the sign out
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/auth/signout', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Declare Packages
  $http = $this('http');
  $lang = $this('lang');
  $config = $this('config');

  //----------------------------//
  // 2. Process Request
  $request->removeSession('me');

  //add a flash
  $response->setSession('flash', [
    'message' => $lang->translate('Sign Out Successful'),
    'type' => 'success'
  ]);

  //redirect
  $redirect = $request->getStage('redirect_uri')
    ?? $config->get('settings', 'home')
    ?? '/';

  $http->redirect($redirect);
});

/* Password Change Routes
-------------------------------- */

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
  $emitter = $this('event');
  $handlebars = $this('handlebars');

  //----------------------------//
  // 2. Security Checks
  //Need to be logged in
  if (!$request->hasSession('me')) {
    //add a flash
    $response->setSession('flash', [
      'message' => $lang->translate('Please sign in first'),
      'type' => 'error'
    ]);

    //redirect
    $redirect = $request->getStage('redirect_uri')
      ?? $config->get('settings', 'home')
      ?? '/';

    $http->redirect($redirect);
  }

  //----------------------------//
  // 3. Prepare Data
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
  // 3. Render Template
  $template = dirname(__DIR__) . '/template';
  if (is_dir($response->getPage('template_root'))) {
    $template = $response->getPage('template_root');
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
  $emitter = $this('event');

  //----------------------------//
  // 2. Setup Overrides
  //determine route
  $route = $request->getStage('route') ?? '/auth/password';

  //determine redirect
  $redirect = $request->getStage('redirect_uri')
    ?? $config->get('settings', 'home')
    ?? '/';

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
  //set the auth_id and profile_id
  $request->setStage('auth_id', $request->getSession('me', 'auth_id'));
  $request->setStage('profile_id', $request->getSession('me', 'profile_id'));

  //remove password if empty
  if (!$request->getStage('auth_password')) {
    $request->removeStage('auth_password');
  }

  if (!$request->getStage('confirm')) {
    $request->removeStage('confirm');
  }

  //----------------------------//
  // 5. Process Request
  //trigger the job
  $emitter->emit('system-object-auth-update', $request, $response);

  //----------------------------//
  // 6. Interpret Results
  if ($response->isError()) {
    return $http->routeTo('get', $route, $request, $response);
  }

  //it was good
  //update the session
  $emitter->emit('system-object-auth-detail', $request, $response);
  $request->setSession('me', $response->getResults());

  //if we dont want to redirect
  if ($redirect === 'false') {
    return;
  }

  //add a flash
  $response->setSession('flash', [
    'message' => $lang->translate('Update successful'),
    'type' => 'success'
  ]);

  $http->redirect($redirect);
});

/* Forgot Routes
-------------------------------- */

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
  $emitter = $this('event');
  $handlebars = $this('handlebars');

  //----------------------------//
  // 2. Prepare Data
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
  // 3. Render Template
  $template = dirname(__DIR__) . '/template';
  if (is_dir($response->getPage('template_root'))) {
    $template = $response->getPage('template_root');
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
    return $this->routeTo('get', $route, $request, $response);
  }

  //----------------------------//
  // 4. Prepare Data
  //----------------------------//
  // 5. Process Request
  //trigger the job
  $emitter->emit('auth-forgot', $request, $response);

  //----------------------------//
  // 6. Interpret Results
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

  //add a flash
  $response->setSession('flash', [
    'message' => $this('lang')->translate(
      'An email with recovery instructions will be sent in a few minutes.'
    ),
    'type' => 'success'
  ]);

  $http->redirect($redirect);
});

/* Verify Routes
-------------------------------- */

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
  $emitter = $this('event');
  $handlebars = $this('handlebars');

  //----------------------------//
  // 2. Prepare Data
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
  // 3. Render Template
  $template = dirname(__DIR__) . '/template';
  if (is_dir($response->getPage('template_root'))) {
    $template = $response->getPage('template_root');
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
 * Process the Verify Page
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
  $route = $request->getStage('route') ?? '/auth/verify';

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
  $emitter->emit('auth-verify', $request, $response);

  //----------------------------//
  // 6. Interpret Results
  if ($response->isError()) {
    //add attempt
    $auth->addAttempt($request);
    return $http->routeTo('get', $route, $request, $response);
  }

  //its good
  $auth->clearAttempts($request);

  //determine redirect
  $redirect = $request->getStage('redirect_uri') ?? '/auth/verify';

  //if we dont want to redirect
  if ($redirect === 'false') {
    return;
  }

  //its good
  $response->setSession('flash', [
    'message' => $this('lang')->translate(
      'An email with verification instructions will be sent in a few minutes.'
    ),
    'type' => 'success'
  ]);

  $http->redirect($redirect);
});

/* OTP Routes
-------------------------------- */

/**
 * Render the OTP Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/auth/otp/:auth_id', function(
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Declare Packages
  $auth = $this('auth');
  $http = $this('http');
  $lang = $this('lang');
  $emitter = $this('event');
  $handlebars = $this('handlebars');

  //----------------------------//
  // 2. Setup Overrides
  //determine route
  $redirect = $request->getStage('redirect_uri')
    ?? $config->get('settings', 'home')
    ?? '/auth/verify';

  //----------------------------//
  // 3. Prepare Data
  //get the detail
  $auth = $emitter->call('system-object-auth-detail', $request);

  if (!$auth) {
    //add a flash
    $response->setSession('flash', [
      'message' => $lang->translate('Invalid account'),
      'type' => 'error'
    ]);

    return $http->redirect($redirect);
  }

  if ($auth['auth_active']) {
    //add a flash
    $response->setSession('flash', [
      'message' => $lang->translate('Account is already verified'),
      'type' => 'error'
    ]);

    return $http->redirect($redirect);
  }

  //----------------------------//
  // 4. Render Template
  $template = dirname(__DIR__) . '/template';
  if (is_dir($response->getPage('template_root'))) {
    $template = $response->getPage('template_root');
  }

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
    ->set('page', 'class', 'page-auth-otp page-auth')
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
$this('http')->post('/auth/otp/:auth_id', function(
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
  $authId = $request->getStage('auth_id');
  $route = $request->getStage('route') ?? sprintf('/auth/otp/%s', $authId);
  //determine redirect
  $redirect = $request->getStage('redirect_uri') ?? '/auth/login';

  //----------------------------//
  // 3. Prepare Data
  $code = $request->getStage('otp');

  //if code does not match
  if ($code != $request->getSession('otp')) {
    $message = $lang->translate('Invalide code');
    $response->setError(true, $message);
    return $http->routeTo('get', $route, $request, $response);
  }

  $request->setStage('auth_active', 1);

  //----------------------------//
  // 4. Process Request
  //trigger the job
  $emitter->trigger('auth-update', $request, $response);

  //----------------------------//
  // 5. Interpret Results
  if ($response->isError()) {
    //add a flash
    $response->setSession('flash', [
      'message' => $response->getMessage(),
      'type' => 'error'
    ]);
    return $http->redirect($route);
  }

  //it was good

  //remove otp
  $request->removeSession('otp');

  //activate session
  if ($request->hasSession('me')) {
    $request->setSession('me', 'auth_active', 1);
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
