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
 * Renders search
 *
 * @param *RequestInterface  $request
 * @param *ResponseInterface $response
 */
$this('http')->get('/admin/system/object/history/search', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $this('handlebars')
    ->registerPartialFromFile('search_bulk', sprintf(
      '/%s/template/search/_bulk.html',
      __DIR__
    ), true)
    ->registerPartialFromFile('search_head', sprintf(
      '/%s/template/search/_head.html',
      __DIR__
    ), true)
    ->registerPartialFromFile('search_links', sprintf(
      '/%s/template/search/_links.html',
      __DIR__
    ), true)
    ->registerPartialFromFile('search_row', sprintf(
      '/%s/template/search/_row.html',
      __DIR__
    ), true);
}, 10);

/**
 * Renders detail
 *
 * @param *RequestInterface  $request
 * @param *ResponseInterface $response
 */
$this('http')->get('/admin/spa/system/object/history/detail/:history_id', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //get the detail at the risk of double calling
  $history = $this('event')->call('system-object-history-detail', [
    'history_id' => $request->getStage('history_id')
  ]);

  if (!$history) {
    return;
  }

  $schema = Schema::load($history['history_object']);

  if ($history['history_from'] && $history['history_to']) {
    $changes = [];
    $fields = $schema->getFields();
    foreach($fields as $name => $field) {
      $from = null;
      if (isset($history['history_from'][$name])) {
        $from = $history['history_from'][$name];
      }

      $to = null;
      if (isset($history['history_to'][$name])) {
        $to = $history['history_to'][$name];
      }

      $changes[$name] = [
        'label' => $field['label'],
        'from' => $from,
        'to' => $to
      ];
    }

    $request->setStage('changes', $changes);
  }

  $this('handlebars')
    ->registerPartialFromFile('detail_tabs', sprintf(
      '/%s/template/detail/_tabs.html',
      __DIR__
    ), true)
    ->registerPartialFromFile('detail_body', sprintf(
      '/%s/template/detail/_body.html',
      __DIR__
    ), true)
    ->registerPartialFromFile('detail_information', sprintf(
      '/%s/template/detail/_information.html',
      __DIR__
    ), true)
    ->registerPartialFromFile('detail_foot', sprintf(
      '/%s/template/detail/_foot.html',
      __DIR__
    ), true);
}, 10);

/**
 * Revert Change
 *
 * @param *RequestInterface  $request
 * @param *ResponseInterface $response
 */
$this('http')->get('/admin/spa/system/object/history/revert/:history_id', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  $data = $request->getStage();

  //get the detail at the risk of double calling
  $history = $this('event')->call('system-object-history-detail', [
    'history_id' => $request->getStage('history_id')
  ], $response);

  if (!$history || $response->isError()) {
    return $this('admin')->invalid($response);
  }

  //set the schema
  $schema = Schema::load('history');

  //set the item
  $item = $response->getResults();

  //set title
  if (!isset($data['title'])) {
    $data['title'] = $this('lang')->translate(
      'Revert %s',
      $schema->getSingular()
    );
  }
  //set message
  if (!isset($data['message'])) {
    $data['message'] = $this('lang')->translate(
      'Are you sure you want to revert %s ?',
      $schema->getSuggestion($item)
    );
  }
  //set the action
  if (!isset($data['action'])) {
    $data['action'] = sprintf(
      '/admin/spa/system/object/%s/revert/%s/confirmed',
      $schema->getName(),
      $item['history_id']
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
}, 10);

/**
 * Revert Change Confirmed
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/admin/spa/system/object/history/revert/:history_id/confirmed', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  //load the schema
  try {
    $schema = Schema::load('history');
  } catch (SystemException $e) {
    return $response->setError(true, $e->getMessage());
  }

  //get the detail at the risk of double calling
  $history = $this('event')->call('system-object-history-detail', [
    'history_id' => $request->getStage('history_id')
  ]);

  if(!$history) {
    return $response->setError(true, 'Not Found');
  }

  $data = $history['history_from'];
  $data['schema'] = $history['history_object'];

  //----------------------------//
  // 2. Process Request
  $this('event')->call('system-object-update', $data, $response);
});
