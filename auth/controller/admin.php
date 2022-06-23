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
 * Render the Admin Settings Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/auth/settings', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Declare Packages
  $http = $this('http');
  $lang = $this('lang');
  $config = $this('config');
  $handlebars = $this('handlebars');

  //----------------------------//
  // 2. Prepare Data
  //get schema data
  $schema = Schema::load('auth');

  //Prepare body
  $data = ['item' => $request->getPost()];

  if (empty($data['item'])) {
    $data['item']['captcha'] = $config->get('services', 'captcha-main') ?? [];
    $data['item']['submission'] = $config->get('auth', 'submission') ?? [];

    foreach ($config->get('services') ?? [] as $service) {
      if (isset($service['type'])
        && ($service['type'] === 'oauth1' || $service['type'] === 'oauth2')
      ) {
        $data['item']['services'][] = $service;
      }
    }
  }

  if ($response->isError()) {
    //add a flash
    $response->setSession('flash', [
      'message' => $response->getMessage(),
      'type' => 'error'
    ]);

    $data['errors'] = $response->getValidation();
    if (isset($data['errors']['service']) && is_array($data['errors']['service'])) {
      foreach ($data['errors']['service'] as $i => $errors) {
        $data['item']['services'][$i]['errors'] = $errors;
      }
    }
  }

  //----------------------------//
  // 3. Render Template
  $template = dirname(__DIR__) . '/template/admin';
  if (is_dir($response->get('page', 'template_root'))) {
    $template = $response->get('page', 'template_root');
  }

  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->registerPartialFromFolder('settings_auth')
    ->registerPartialFromFolder('settings_captcha')
    ->registerPartialFromFolder('settings_services')
    ->renderFromFolder('settings', $data);

  //if we only want the body
  if ($request->getStage('render') === 'body') {
    return;
  }

  //set content
  $response
    ->set('page', 'title', $this('lang')->translate('Authentication Settings'))
    ->set('page', 'class', 'page-admin-auth-settings page-admin')
    ->setContent($body);

  //render page
  $this('admin')->render($request, $response);
});

/**
 * Process the Admin Settings Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/admin/auth/settings', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Declare Packages
  $http = $this('http');
  $lang = $this('lang');
  $config = $this('config');
  $handlebars = $this('handlebars');

  //----------------------------//
  // 2. Prepare Data
  $services = $config->get('services');
  $data['captcha'] = $request->getStage('captcha') ?? [];
  $data['services'] = $request->getStage('services') ?? [];
  $data['submission'] = $request->getStage('submission') ?? [];

  //----------------------------//
  // 3. Validate Data
  foreach ($data['services'] as $i => $service) {
    if (!isset($service['name']) || !trim((string) $service['name'])) {
      $response->invalidate('service', $i, 'name', 'Name is required');
    }

    if (!isset($service['active'])) {
      $response->invalidate('service', $i, 'active', 'Active is required');
    }

    if (!isset($service['client_id']) || !trim((string) $service['client_id'])) {
      $response->invalidate('service', $i, 'client_id', 'Client ID is required');
    }

    if (!isset($service['client_secret']) || !trim((string) $service['client_secret'])) {
      $response->invalidate('service', $i, 'client_secret', 'Client Secret is required');
    }

    if (!isset($service['url_authorize']) || !trim((string) $service['url_authorize'])) {
      $response->invalidate('service', $i, 'url_authorize', 'Authorize URL is required');
    }

    if (!isset($service['url_access_token']) || !trim((string) $service['url_access_token'])) {
      $response->invalidate('service', $i, 'url_access_token', 'Access Token URL is required');
    }

    if (!isset($service['url_resource']) || !trim((string) $service['url_resource'])) {
      $response->invalidate('service', $i, 'url_resource', 'Resource URL is required');
    }
  }

  if (!$response->isValid()) {
    $response->setError(true, 'Invalid parameters');
    return $http->routeTo('get', '/admin/auth/settings', $request, $response);
  }

  //----------------------------//
  // 4. Process Request
  foreach ($data['services'] as $service) {
    $key = $this('auth')->slugger($service['name'], 'main');
    $service['active'] = (bool) $service['active'];
    $service['type'] = 'oauth2';
    $services[$key] = $service;
  }

  $services['captcha-main']['active'] = (bool) $data['captcha']['active'];
  $services['captcha-main']['token'] = $data['captcha']['token'] ?? null;
  $services['captcha-main']['secret'] = $data['captcha']['token'] ?? null;

  $config->set('services', $services);

  $settings = ['captcha' => 2, 'lockout' => 4, 'wait' => 5];
  foreach ($settings as $setting => $default) {
    if (!is_numeric($data['submission'][$setting])) {
      $data['submission'][$setting] = $default;
    }

    $data['submission'][$setting] = (int) $data['submission'][$setting];
  }

  $this('config')->set('auth', 'submission', $data['submission']);

  //----------------------------//
  // 5. Interpret Results
  //redirect
  $redirect = $request->getStage('redirect_uri') ?? '/admin/auth/settings';

  //if we dont want to redirect
  if ($redirect === 'false') {
    return;
  }

  //add a flash
  //add a flash
  $response->setSession('flash', [
    'message' => $lang->translate('Authentication settings were updated'),
    'type' => 'success'
  ]);

  $http->redirect($redirect);
});

/**
 * Check for profile on auth create
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/admin/spa/system/object/auth/create', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //if profile id, or profile name
  if (is_numeric($request->getStage('profile_id'))
    || !trim((string) $request->getStage('__profile'))
  ) {
    return;
  }

  //----------------------------//
  // 1. Get Data
  $name = $request->getStage('__profile');
  if (strpos($name, '  ') !== false) {
    [ $first, $last ] = explode('  ', $name, 2);
  } else if (strpos($name, ' ') !== false) {
    [ $first, $last ] = explode(' ', $name, 2);
  } else {
    $first = $name;
    $last = ' ';
  }

  $data = [];
  if (is_array($request->getStage())) {
    $data = $request->getStage();
  }

  //----------------------------//
  // 2. Validate Data
  $auth = Schema::load('auth');
  $errors = $auth->getErrors($data);

  //if there are errors
  if (!empty($errors)) {
    //let the original route take care of this
    return;
  }

  // trigger model create
  $this('event')->call('system-object-profile-create', [
    'profile_first_name' => $first,
    'profile_last_name' => $last
  ], $response);
  //if there are errors
  if ($response->isError()) {
    //let the original route take care of this
    return;
  }

  // set profile id
  $request->setStage('profile_id', $response->getResults());
}, 10);
