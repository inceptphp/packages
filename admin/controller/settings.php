<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

/**
 * Render the settings page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/settings', function(
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  $data = $this('config')->get('settings');

  //----------------------------//
  // 2. Render Template
  $template = dirname(__DIR__) . '/template/settings';
  if (is_dir($response->get('page', 'template_root'))) {
    $template = $response->get('page', 'template_root');
  }

  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->renderFromFolder('form', $data);

  //if we only want the body
  if ($request->getStage('render') === 'body') {
    return;
  }

  //set content
  $response
    ->set('page', 'title', $this('lang')->translate('Settings'))
    ->set('page', 'class', 'page-admin-settings page-admin')
    ->setContent($body);

  //render page
  $this('admin')->render($request, $response);
});

/**
 * Process the settings page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/admin/settings', function(
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  $settings = $this('config')->get('settings');

  $settings['name'] = $request->getStage('name');
  $settings['logo'] = $request->getStage('logo');
  $settings['email'] = $request->getStage('email');

  $settings['timezone'] = $request->getStage('timezone');
  $settings['language'] = $request->getStage('language');
  $settings['currency'] = $request->getStage('currency');

  $settings['home'] = $request->getStage('home');
  $settings['https'] = $request->getStage('https');
  $settings['environment'] = $request->getStage('environment');
  $settings['debug_mode'] = (int) $request->getStage('debug_mode');

  //----------------------------//
  // 2. Process Data
  $this('config')->set('settings', $settings);

  $redirect = '/admin/settings';
  if ($request->getStage('redirect_uri')) {
    $redirect = $request->getStage('redirect_uri');
  }

  //add a flash
  $response->setSession('flash', [
    'message' => $this('lang')->translate('Settings Saved.'),
    'type' => 'success'
  ]);

  return $this('http')->redirect($redirect);
});
