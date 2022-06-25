<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

use Incept\Framework\Schema;

/**
 * Render App Search Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/system/object/app/detail/:app_id', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $template = __DIR__ . '/template/detail/_information.html';
  $this('handlebars')->registerPartialFromFile('detail_information', $template, true);
}, 10);

/**
 * Render app refresh screen
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/system/object/app/refresh/:app_id', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  $data = $request->getStage();

  //get the original table row
  $this('event')->emit('system-object-app-detail', $request, $response);

  //if we only want the raw data
  if ($request->getStage('render') === 'false') {
    return;
  }

  //can we view ?
  if ($response->isError()) {
    return $this('admin')->invalid($response);
  }

  //set the item
  $item = $response->getResults();

  //set title
  if (!isset($data['title'])) {
    $data['title'] = $this('lang')->translate('Refresh Tokens');
  }
  //set message
  if (!isset($data['message'])) {
    $data['message'] = $this('lang')->translate(
      'Are you sure you want to refresh the app tokens?'
    );
  }
  //set the action
  if (!isset($data['action'])) {
    $data['action'] = sprintf(
      '/admin/spa/system/object/app/refresh/%s/confirmed',
      $item['app_id']
    );
  }
  //after submit what then?
  if (!isset($data['after'])) {
    $data['after'] = 'reload';
  }

  //----------------------------//
  // 2. Render Template
  $template = __DIR__ . '/template';
  if (is_dir($response->get('page', 'template_root') ?? '')) {
    $template = $response->get('page', 'template_root');
  }

  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->renderFromFolder('confirm', $data);

  //set content
  $response->setContent($body);
});

/**
 * Process the app refresh
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/admin/spa/system/object/app/refresh/:app_id/confirmed', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  //----------------------------//
  // 2. Process Request
  $this('event')->call('system-object-app-update', [
    'app_id' => $request->getStage('app_id'),
    'app_token' => uniqid(),
    'app_secret' => uniqid()
  ], $response);
});
