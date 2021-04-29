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
    $item['service']['facebook'] = $config->get('services', 'facebook-main');
    $item['service']['twitter'] = $config->get('services', 'twitter-main');
    $item['service']['linkedin'] = $config->get('services', 'linkedin-main');
    $item['service']['google'] = $config->get('services', 'google-main');
    $item['service']['captcha'] = $config->get('services', 'captcha-main');
    $item['submission'] = $config->get('auth', 'submission');

    foreach ($item['service'] as $service => $setting) {
      if (!is_array($setting)) {
        continue;
      }

      $item['service'][$service] = $setting;
    }

    $data['item'] = $item;
  }

  if ($response->isError()) {
    //add a flash
    $response->setSession('flash', [
      'message' => $response->getMessage(),
      'type' => 'error'
    ]);

    $data['errors'] = $response->getValidation();
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
    ->registerPartialFromFolder('settings_facebook')
    ->registerPartialFromFolder('settings_google')
    ->registerPartialFromFolder('settings_linkedin')
    ->registerPartialFromFolder('settings_twitter')
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
  $data['services'] = $request->getStage('service') ?? [];
  $data['submission'] = $request->getStage('submission') ?? [];

  //----------------------------//
  // 3. Validate Data
  //----------------------------//
  // 4. Process Request
  foreach ($data['services'] as $name => $service) {
    foreach ($service as $key => $value) {
      if ($key === 'active') {
        $service[$key] = (bool) $service[$key];
        continue;
      }
      if (!trim($value)) {
        $service[$key] = sprintf('<%s %s>', strtoupper($name), strtoupper($key));
      }
    }

    $services[$name . '-main'] = $service;
  }

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
  $redirect = $request->getStage('redirect_uri') ?? '/admin/system/object/auth/settings';

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
