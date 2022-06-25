<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Incept\Framework\Schema;
use Incept\Framework\Fieldset;

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

/* Remove Routes
-------------------------------- */

/**
 * Process the System Model Remove
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/system/collection/:schema/remove', function (
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
  $primary = $schema->getPrimaryName();
  $data['schema'] = $schema->get();
  $data['schema']['primary'] = $primary;

  if (!isset($data[$primary])
    || !is_array($data[$primary])
    || empty($data[$primary])
  ) {
    $response->setError(true, 'No items selected.');
    return $this('admin')->invalid($response);
  }

  //set dynamic keys to static
  $data['ids'] = $data[$primary];

  //----------------------------//
  // 2. Render Template
  $template = dirname(__DIR__) . '/template/collection';
  if (is_dir($response->get('page', 'template_root') ?? '')) {
    $template = $response->get('page', 'template_root');
  }

  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->renderFromFolder('remove', $data);

  //set content
  $response->setContent($body);
});

/**
 * Process the System Model Remove
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/admin/spa/system/collection/:schema/remove/confirmed', function (
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
  $this('event')->emit('system-collection-remove', $request, $response);
});

/* Restore Routes
-------------------------------- */

/**
 * Process the System Model Restore
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/system/collection/:schema/restore', function (
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
  $primary = $schema->getPrimaryName();
  $data['schema'] = $schema->get();
  $data['schema']['primary'] = $primary;

  if (!isset($data[$primary])
    || !is_array($data[$primary])
    || empty($data[$primary])
  ) {
    $response->setError(true, 'No items selected.');
    return $this('admin')->invalid($response);
  }

  //set dynamic keys to static
  $data['ids'] = $data[$primary];

  //----------------------------//
  // 2. Render Template
  $template = dirname(__DIR__) . '/template/collection';
  if (is_dir($response->get('page', 'template_root') ?? '')) {
    $template = $response->get('page', 'template_root');
  }

  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->renderFromFolder('restore', $data);

  //set content
  $response->setContent($body);
});

/**
 * Process the System Model Restore
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/admin/spa/system/collection/:schema/restore/confirmed', function (
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
  $this('event')->emit('system-collection-restore', $request, $response);
});

/* Unlink Routes
-------------------------------- */

/**
 * Process the System Model Remove
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/system/collection/:schema1/:id/unlink/:schema2', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  $data = $request->getStage();

  //get schema and relation
  try {
    $schema = Schema::load($request->getStage('schema1'));
  } catch (SystemException $e) {
    $response->setError(true, $e->getMessage());
    return $this('admin')->invalid($response);
  }

  $relation = $schema->getRelations(null, $request->getStage('schema2'));

  if (empty($relation)) {
    $response->setError(true, 'Cannot find a valid relation.');
    return $this('admin')->invalid($response);
  }

  //results will look like [profile_product => object]
  $table = array_key_first($relation);
  $relation = $relation[$table];

  $primary = $schema->getPrimaryName();
  $data['schema'] = $schema->get();
  $data['schema']['primary'] = $primary;

  $primary = $relation->getPrimaryName();
  $data['relation'] = $relation->get();
  $data['relation']['primary'] = $primary;

  if (!isset($data[$primary])
    || !is_array($data[$primary])
    || empty($data[$primary])
  ) {
    $response->setError(true, 'No items selected.');
    return $this('admin')->invalid($response);
  }

  //set dynamic keys to static
  $data['ids'] = $data[$primary];

  //----------------------------//
  // 2. Render Template
  $template = dirname(__DIR__) . '/template/collection';
  if (is_dir($response->get('page', 'template_root') ?? '')) {
    $template = $response->get('page', 'template_root');
  }

  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->renderFromFolder('unlink', $data);

  //set content
  $response->setContent($body);
});

/**
 * Process the System Model Remove
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/admin/spa/system/collection/:schema1/:id/unlink/:schema2/confirmed', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  //get schema and relation
  try {
    $schema = Schema::load($request->getStage('schema1'));
  } catch (SystemException $e) {
    return $response->setError(true, $e->getMessage());
  }

  $relation = $schema->getRelations(null, $request->getStage('schema2'));

  if (empty($relation)) {
    return $response->setError(true, 'Cannot find a valid relation.');
  }

  $id = $request->getStage('id');
  $primary = $schema->getPrimaryName();

  $request->setStage($primary, $id);

  //----------------------------//
  // 2. Process Request
  $this('event')->emit('system-collection-unlink', $request, $response);
});

/* Update Routes
-------------------------------- */

/**
 * Render the System Model Update Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/system/collection/:schema/update', function (
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

  //make a blank item
  $data['item'] = [];

  //also pass the schema to the template
  $primary = $schema->getPrimaryName();
  $data['schema'] = $schema->get();
  $data['schema']['fields'] = $schema->getFields();
  $data['schema']['restorable'] = $schema->isRestorable();
  $data['schema']['primary'] = $primary;

  //set dynamic keys to static
  $data['ids'] = $data[$primary];

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

  //if we only want the data
  if ($request->getStage('render') === 'false') {
    return $response->setResults($data);
  }

  //set title
  if (!isset($data['title'])) {
    $data['title'] = $this('lang')->translate(
      'Update %s',
      $data['schema']['plural']
    );
  }
  //set the action
  if (!isset($data['action'])) {
    $data['action'] = sprintf(
      '/admin/spa/system/collection/%s/update',
      $data['schema']['name']
    );
  }
  //after submit what then?
  if (!isset($data['after'])) {
    $data['after'] = 'reload';
  }

  //----------------------------//
  // 2. Render Template
  $template = dirname(__DIR__) . '/template/object';
  if (is_dir($response->get('page', 'template_root') ?? '')) {
    $template = $response->get('page', 'template_root');
  }

  //render the body
  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->registerPartialFromFolder('form_fieldset', 'html', true)
    ->registerPartialFromFolder('form_information', 'html', true)
    ->registerPartialFromFolder('form_tabs', 'html', true)
    ->renderFromFolder('form', $data);

  //set content
  $response->setContent($body);
});

/**
 * Process the System Model Update Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/admin/spa/system/collection/:schema/update', function (
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

  //get the primary data now
  $primary = $schema->getPrimaryName();
  //get the IDs of the object
  $ids = $request->getStage($primary);
  if (!is_array($ids) || !count($ids)) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->invalidate($primary, 'No IDs given');
  }

  $request->setStage('in', $primary, $ids);
  $request->removeStage($primary);

  //----------------------------//
  // 2. Process Request
  $this('event')->emit('system-collection-update', $request, $response);
});

/* Import/Export Routes
-------------------------------- */

/**
 * Process Object Import
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/admin/system/collection/:schema/import', function (
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

  $primary = $schema->getPrimaryName();

  // data
  $data = $request->getStage();
  $rows = $data['rows'];
  unset($data['rows']);

  $toInsert = $toUpdate = $indexes = [];

  foreach ($rows as $i => $row) {
    $row = array_merge($row, $data);
    if (isset($row[$primary]) && is_numeric($row[$primary])) {
      $indexes['update'][count($toUpdate) - 1] = $i;
      $toUpdate[] = $row;
    } else {
      $indexes['insert'][count($toUpdate) - 1] = $i;
      $toInsert[] = $row;
    }
  }

  // set data
  $request->setStage('rows', $data);

  //----------------------------//
  // 2. Process Request
  $errors = [];
  if (!empty($toInsert)) {
    $payload = $this->makePayload(false);
    $payload['request']
      ->setStage('schema', $schema->getName())
      ->setStage('rows', $toInsert);

    //mass insert
    $this('event')->call(
      'system-collection-create',
      $payload['request'],
      $payload['response']
    );
    //if errors
    if ($payload['response']->isError()
      && is_array($payload['response']->getValidation('rows'))
    ) {
      //loop through errors
      foreach ($payload['response']->getValidation('rows') as $i => $row) {
        //get the real index
        if (isset($indexes['insert'][$i])) {
          $errors[$indexes['insert'][$i]] = $row;
        }
      }
    }
  }

  if (!empty($toUpdate)) {
    foreach ($toUpdate as $i => $row) {
      $payload = $this->makePayload(false);
      $payload['request']
        ->setStage($row)
        ->setStage('schema', $schema->getName());

      $this('event')->call(
        'system-object-update',
        $payload['request'],
        $payload['response']
      );

      //if errors
      if ($payload['response']->isError()
        && is_array($payload['response']->getValidation())
        && isset($indexes['update'][$i])
      ) {
        $errors[$indexes['update'][$i]] = $payload['response']->getValidation();
      }
    }
  }

  //----------------------------//
  // 3. Interpret Results

  if (!empty($errors)) {
    $response->setError(true, 'Some rows are invalid');
    $response->set('json', 'validation', $errors);
  } else {
    $response->setError(false);
  }
});

/**
 * Process Object Export
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/system/collection/:schema/export', function (
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

  $data = [];

  //load system package
  $system = $this('system');
  ///get just filters
  $data['filters'] = $system->getQuery($request->getStage());

  $data['schema'] = $schema->get();
  //generate a file field
  $data['field'] =

  //----------------------------//
  // 2. Render Template
  $data['title'] = $schema->getPlural('plural');

  $template = dirname(__DIR__) . '/template/collection';
  if (is_dir($response->get('page', 'template_root') ?? '')) {
    $template = $response->get('page', 'template_root');
  }

  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->renderFromFolder('export', $data);

  //set content
  $response->setContent($body);
});

/**
 * Process Object Export
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/admin/system/collection/:schema/export', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //preset the redirect
  $redirect = sprintf(
    '/admin/system/object/%s/search',
    $request->getStage('schema1')
  );

  //if there is a specified redirect
  if ($request->getStage('redirect_uri')) {
    //set the redirect
    $redirect = $request->getStage('redirect_uri');
  }

  //----------------------------//
  // 1. Prepare Data
  //load the schema
  try {
    $schema = Schema::load($request->getStage('schema'));
  } catch (SystemException $e) {
    return $this('http')->redirect($redirect . '?error=' . $e->getMessage());
  }

  //load system package
  $system = $this('system');
  //clean stage, this will remove possible SQL injections
  $system->cleanStage($request);

  if (!$request->hasStage('start')) {
    $request->setStage('start', 0);
  }

  if (!$request->hasStage('range')) {
    $request->setStage('range', 0);
  }

  //----------------------------//
  // 2. Process Request
  $this('event')->emit('system-collection-search', $request, $response);

  //----------------------------//
  // 3. Interpret Results
  //get the output type
  $type = $request->getStage('type');
  //get the rows
  $rows = $response->getResults('rows');
  //determine the filename
  $filename = $schema->getPlural() . '-' . date('Y-m-d');

  if ($type === 'csv') {
    //set the output headers
    $response
      ->addHeader('Content-Encoding', 'UTF-8')
      ->addHeader('Content-Type', 'text/csv; charset=UTF-8')
      ->addHeader('Content-Disposition', 'attachment; filename=' . $filename . '.csv');

    $rows = $this('admin')->rowsToCsv($rows);

    //open a tmp file
    $file = tmpfile();
    //for each row
    foreach ($rows as $row) {
      //add it to the tmp file as a csv
      fputcsv($file, $row);
    }

    //this is the final output
    $contents = '';

    //rewind the file pointer
    rewind($file);
    //and set all the contents
    while (!feof($file)) {
      $contents .= fread($file, 8192);
    }

    //close the tmp file
    fclose($file);

    //set contents
    return $response->setContent($contents);
  }

  //if the output type is xml
  if ($type === 'xml') {
    //set the output headers
    $response
      ->addHeader('Content-Encoding', 'UTF-8')
      ->addHeader('Content-Type', 'text/xml; charset=UTF-8')
      ->addHeader('Content-Disposition', 'attachment; filename=' . $filename . '.xml');

    //get the contents
    $contents = $this('admin')->rowsToXml($rows, $schema->getName());

    //set the contents
    return $response->setContent($contents);
  }

  //by default JSON

  //set the output headers
  $response
    ->addHeader('Content-Encoding', 'UTF-8')
    ->addHeader('Content-Type', 'text/json; charset=UTF-8')
    ->addHeader('Content-Disposition', 'attachment; filename=' . $filename . '.json');

  //set content
  return $response->set('json', $rows);
});
