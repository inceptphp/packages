<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

/* Search Routes
-------------------------------- */

/**
 * Render the language search page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/language/search', function(
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  $data = [];

  $folder = $this('config')->getFolder('language');
  //loop through all the php files
  foreach (glob(sprintf('%s/*.php', $folder)) as $file) {
    $data['rows'][] = basename($file, '.php');
  }

  //----------------------------//
  // 2. Render Template
  $template = dirname(__DIR__) . '/template/language';
  if (is_dir($response->getPage('template_root'))) {
    $template = $response->getPage('template_root');
  }

  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->registerPartialFromFolder('search_head')
    ->registerPartialFromFolder('search_links')
    ->registerPartialFromFolder('search_row')
    ->renderFromFolder('search', $data);

  //if we only want the body
  if ($request->getStage('render') === 'body') {
    return;
  }

  //set content
  $response
    ->set('page', 'title', $this('lang')->translate('Languages'))
    ->set('page', 'class', 'page-admin-language-search page-admin')
    ->setContent($body);

  //render page
  $this('admin')->render($request, $response);
});

/* Create Routes
-------------------------------- */

/**
 * Render the language create page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/language/create', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  $data = $request->getStage();

  //if we only want the data
  if ($request->getStage('render') === 'false') {
    return $response->setResults($data);
  }

  //set title
  if (!isset($data['title'])) {
    $data['title'] = $this('lang')->translate('Add Language');
  }
  //set the action
  if (!isset($data['action'])) {
    $data['action'] = '/admin/spa/language/create';
  }
  //after submit what then?
  if (!isset($data['after'])) {
    $data['after'] = 'reload';
  }

  //----------------------------//
  // 2. Render Template
  $template = dirname(__DIR__) . '/template/language';
  if (is_dir($response->getPage('template_root'))) {
    $template = $response->getPage('template_root');
  }

  //render the body
  $body = $this('handlebars')
    ->setTemplateFolder($template)
    //->registerPartialFromFolder('form_tabs')
    ->renderFromFolder('form', $data);

  //set content
  $response->setContent($body);
});

/**
 * Render the language copy page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/language/create/:language', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  //load the schema
  try {
    $schema = Schema::load($request->getStage('schema'));
  } catch (SystemException $e) {
    $response->setError(true, $e->getMessage());
    return $this('admin')->invalid($response);
  }

  $data = $request->getStage();

  //also pass the schema to the template
  $data['schema'] = $schema->get();
  $data['schema']['fields'] = $schema->getFields();
  $data['schema']['primary'] = $primary = $schema->getPrimaryName();
  $data['schema']['restorable'] = $schema->isRestorable();

  //split fields and fieldsets
  foreach ($data['schema']['fields'] as $key => $field) {
    if ($field['field']['type'] !== 'fieldset'
      || !isset($field['field']['parameters'][0])
    ) {
      continue;
    }

    //it's a fieldset, remove it from the field list
    unset($data['schema']['fields'][$key]);
    //add it to the fieldset
    $data['schema']['fieldsets'][$key] = $field;
    $data['schema']['fieldsets'][$key]['fieldset'] = Fieldset::load(
      $field['field']['parameters'][0]
    )->get();
  }

  //set relation schemas 1:1
  $relations = $schema->getRelations(1);
  foreach ($relations as $table => $relation) {
    $data['relations'][$table] = $relation->get();
    $data['relations'][$table]['primary'] = $relation->getPrimaryName();
  }

  //add relation schemas 0:1
  $relations = $schema->getRelations(0);
  foreach ($relations as $table => $relation) {
    $data['relations'][$table] = $relation->get();
    $data['relations'][$table]['primary'] = $relation->getPrimaryName();
  }

  //table_id, 1 for example
  $id = $request->getStage('id');
  $request->setStage($primary, $id);

  //get the original table row
  $this('event')->emit('system-object-detail', $request, $response);

  //if we only want the raw data
  if ($request->getStage('render') === 'false') {
    return;
  }

  //can we view ?
  if ($response->isError()) {
    return $this('admin')->invalid($response);
  }

  //set the item
  $data['item'] = $response->getResults();

  //----------------------------//
  // 2. Render Template
  //set the action
  $data['action'] = 'create';

  $template = dirname(__DIR__) . '/template/object';
  if (is_dir($response->getPage('template_root'))) {
    $template = $response->getPage('template_root');
  }

  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->registerPartialFromFolder('form_fieldset')
    ->registerPartialFromFolder('form_information')
    ->registerPartialFromFolder('form_tabs')
    ->renderFromFolder('form', $data);

  //set content
  $response->setContent($body);
});

/**
 * Process the language create page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/admin/spa/language/create', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  //load the schema
  try {
    $schema = Schema::load($request->getStage('schema'));
  } catch (SystemException $e) {
    return $response->setError(true, $e->getMessage());
  }

  //----------------------------//
  // 2. Process Request
  $this('event')->emit('system-object-create', $request, $response);
});
