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
 * Render admin dashboard
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin', function ($request, $response) {
  //----------------------------//
  // 1. Prepare body
  //load some packages
  $config = $this('config');
  $emiter = $this('event');
  $language = $this('lang');
  //get the data
  $data = $request->getStage();

  // get schemas
  $emiter->emit('system-schema-search', $request, $response);

  // schemas
  foreach ($response->getResults('rows') as $schema) {
    $data['schemas'][$schema['name']] = [
      'name' => $schema['name'],
      'label' => $schema['plural'],
      'path' => sprintf('/admin/system/object/%s/search', $schema['name']),
      'records' => 0
    ];
  }

  // get the database name
  $key = $config->get('settings', 'pdo');
  $name = $config->get('services', $key, 'name');

  // get the record count
  $records = $emiter->call('system-store-search', [
    'table' => 'INFORMATION_SCHEMA.TABLES',
    'columns' => 'table_name, table_rows',
    'filters' => [
      [ 'where' => 'TABLE_SCHEMA =%s', 'binds' => [ $name ] ]
    ]
  ]);

  // on each record
  foreach ($records as $record) {
    $name = null;
    $rows = null;

    //mysql 5.8
    if (isset($record['TABLE_NAME'])) {
      $name = $record['TABLE_NAME'];
      $rows = $record['TABLE_ROWS'];

    //mysql <= 5.7
    } else {
      $name = $record['table_name'];
      $rows = $record['table_rows'];
    }

    if (isset($schemas[$name])) {
      $data['schemas'][$name]['records'] = $rows;
    }
  }

  //----------------------------//
  // 2. Render Template
  $template = dirname(__DIR__) . '/template/view';
  if (is_dir($response->getPage('template_root'))) {
    $template = $response->getPage('template_root');
  }

  $body = $this('handlebars')
    ->setTemplateFolder($template)
    //->registerPartialFromFolder('search_row')
    ->renderFromFolder('dashboard', $data);

  //if we only want the body
  if ($request->getStage('render') === 'body') {
    return;
  }

  //set content
  $response
    ->set('page', 'title', $this('lang')->translate('Dashboard'))
    ->set('page', 'class', 'page-admin-dashboard page-admin')
    ->setContent($body);

  //render page
  $this('admin')->render($request, $response);
});
