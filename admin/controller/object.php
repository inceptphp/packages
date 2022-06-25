<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Incept\Framework\Schema;
use Incept\Framework\Fieldset;
use Incept\Framework\SystemException;

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

/* Search Routes
-------------------------------- */

/**
 * Render the System Model Search Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/system/object/:schema/search', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  //load the schema
  try {
    $schema = Schema::load($request->getStage('schema'));
  } catch (SystemException $e) {
    //let it 404 on error
    return;
  }

  //set a default range
  if (!$request->hasStage('range')) {
    $request->setStage('range', 50);
  }

  //load system package
  $system = $this('system');
  //clean stage, this will remove possible SQL injections
  $system->cleanStage($request);

  //trigger job
  $this('event')->emit('system-collection-search', $request, $response);

  //if we only want the raw data
  if ($request->getStage('render') === 'false') {
    return;
  }

  //form the data
  $data = array_merge(
    $request->getStage(),
    $response->getResults()
  );

  //get just filters
  $data['filters'] = $system->getQuery($request->getStage());

  //also pass the schema to the template
  $data['schema'] = $schema->get();
  $data['schema']['fields'] = $schema->getFields();
  $data['schema']['primary'] = $primary = $schema->getPrimaryName();
  $data['schema']['restorable'] = $schema->isRestorable();
  $data['active'] = 1;
  $data['uuid'] = uniqid();

  //we need active to determine if we should add a filter
  $active = $schema->getFields('active');
  if (!empty($active)) {
    //get the active field name
    $active = array_keys($active)[0];
    //add to schema
    $data['schema']['active'] = $active;
    //if primary filter
    if (isset($data['filters']['filter'][$active])) {
      //get all active rows
      $data['active'] = $data['filters']['filter'][$active];
    }
  }

  //----------------------------//
  // 2. Render Template
  $data['title'] = $schema->getPlural('plural');

  $template = dirname(__DIR__) . '/template/object';
  if (is_dir($response->get('page', 'template_root') ?? '')) {
    $template = $response->get('page', 'template_root');
  }

  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->registerPartialFromFolder('search_bulk', 'html', true)
    ->registerPartialFromFolder('search_filters', 'html', true)
    ->registerPartialFromFolder('search_form', 'html', true)
    ->registerPartialFromFolder('search_head', 'html', true)
    ->registerPartialFromFolder('search_links', 'html', true)
    ->registerPartialFromFolder('search_row', 'html', true)
    ->renderFromFolder('search', $data);

  //set content
  $response
    ->set('page', 'title', $data['title'])
    ->set('page', 'class', 'page-admin-system-object-search page-admin')
    ->setContent($body);

  //if we only want the body
  if ($request->getStage('render') === 'body') {
    return;
  }

  //render page
  $this('admin')->render($request, $response);
});

/**
 * Render the System Model Search Page Filtered by Relation
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/system/object/:schema1/:id/search/:schema2', function (
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
  //get schema and relation
  try {
    $schema = Schema::load($request->getStage('schema1'));
  } catch (SystemException $e) {
    //add a flash and redirect
    return $this('http')->redirect($redirect . '?error=' . $e->getMessage());
  }

  $relation = $schema->getRelations(null, $request->getStage('schema2'));

  if (empty($relation)) {
    //add a flash and redirect
    $response->setSession('flash', [
      'message' => $this('lang')->translate('Cannot find a valid relation.'),
      'type' => 'error'
    ]);
    return $this('http')->redirect($redirect);
  }

  //results will look like [profile_product => object]
  $table = array_key_first($relation);
  $relation = $relation[$table];

  //set up the search filter
  $id = $request->getStage('id');
  $primary = $schema->getPrimaryName();
  $request->setStage('filter', $primary, $id);

  //remove the data from stage
  //because we wont need it anymore
  $request
    ->removeStage('id')
    ->removeStage('schema1')
    ->removeStage('schema2');

  //get the schema detail
  $payload = $request->clone(true);

  $payload
    //let the event know what schema we are using
    ->setStage('schema', $schema->getName())
    //table_id, 1 for example
    ->setStage($primary, $id);

  //now get the actual table row
  $results = $this('event')->call('system-object-detail', $payload);

  //and determine the title of the table row
  //this will be used on the breadcrumbs and title for example
  $suggestion = $schema->getSuggestion($results);

  //pass all the relational data we collected
  $request
    ->setStage('relation', $relation->get())
    ->setStage('relation', 'schema', $schema->get())
    ->setStage('relation', 'schema', 'id', $id)
    ->setStage('relation', 'schema', 'primary', $primary)
    ->setStage('relation', 'item', $results)
    ->setStage('relation', 'suggestion', $suggestion);

  //----------------------------//
  // 2. Render Template
  $route = sprintf('/admin/system/object/%s/search', $relation->getName());
  //now let the original search take over
  $this('http')->routeTo('get', $route, $request, $response);
});

/* Detail Routes
-------------------------------- */

/**
 * Render the System Model Detail Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/system/object/:schema/detail/:id', function (
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

  //table_id, 1 for example
  $id = $request->getStage('id');
  $primary = $schema->getPrimaryName();
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
  $data['suggestion'] = $schema->getSuggestion($data['item']);

  //set a uuid for tabs
  $data['uuid'] = uniqid();

  //we need active to determine if we should update or delete
  $data['active'] = 1;
  $active = $schema->getFields('active');
  if (!empty($active)) {
    //get the active field name
    $active = array_keys($active)[0];
    if (isset($data['item'][$active])) {
      $data['active'] = $data['item'][$active];
    }
  }

  //also pass the schema to the template
  $data['schema'] = $schema->get();
  $data['schema']['id'] = $id;
  $data['schema']['fields'] = $schema->getFields();
  $data['schema']['primary'] = $primary;
  $data['schema']['restorable'] = $schema->isRestorable();

  //set relation schemas
  $relations = $schema->getRelations();
  foreach ($relations as $table => $relation) {
    $name = $relation->getName();
    $primary = $relation->getPrimaryName();

    $data['relations'][$table] = $relation->get();
    //pass the following for scope convenience
    $data['relations'][$table]['primary'] = $primary;
    $data['relations'][$table]['uuid'] = $data['uuid'];
    $data['relations'][$table]['schema'] = $data['schema'];

    //! 1:1 ?
    if (isset($data['item'][$name])) {
      $data['relations'][$table]['suggestion'] = $relation->getSuggestion(
        $data['item'][$name]
      );
      if ($relation->getMany() > 1) {
        $data['relations'][$table]['rows'] = $data['item'][$name];
      } else if (isset($data['item'][$name][$primary])) {
        $data['relations'][$table]['id'] = $data['item'][$name][$primary];
        $data['relations'][$table]['item'] = $data['item'][$name];
      }
    //1:1 ?
    } else if (isset($data['item'][$primary])) {
      $data['relations'][$table]['id'] = $data['item'][$primary];
    }
  }

  //----------------------------//
  // 2. Render Template
  $template = dirname(__DIR__) . '/template/object';
  if (is_dir($response->get('page', 'template_root') ?? '')) {
    $template = $response->get('page', 'template_root');
  }

  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->registerPartialFromFolder('detail_body', 'html', true)
    ->registerPartialFromFolder('detail_foot', 'html', true)
    ->registerPartialFromFolder('detail_head', 'html', true)
    ->registerPartialFromFolder('detail_information', 'html', true)
    ->registerPartialFromFolder('detail_relation_detail', 'html', true)
    ->registerPartialFromFolder('detail_relation_search', 'html', true)
    ->registerPartialFromFolder('detail_relation_search_bulk', 'html', true)
    ->registerPartialFromFolder('detail_relation_search_head', 'html', true)
    ->registerPartialFromFolder('detail_relation_search_links', 'html', true)
    ->registerPartialFromFolder('detail_relation_search_row', 'html', true)
    ->registerPartialFromFolder('detail_tabs', 'html', true)
    ->renderFromFolder('detail', $data);

  //set content
  $response->setContent($body);
});

/**
 * Render the System Model Detail Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/system/object/:schema1/:id1/:schema2/detail/:id2', function (
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

  //remove the data from stage
  //because we wont need it anymore
  $request
    ->removeStage('id1')
    ->removeStage('id2')
    ->removeStage('schema1')
    ->removeStage('schema2');

  //get the schema detail
  $payload = $request->clone(true);

  $payload
    //let the event know what schema we are using
    ->setStage('schema', $schema->getName())
    //table_id, 1 for example
    ->setStage($schema->getPrimaryName(), $data['id1']);

  //now get the actual table row
  $results = $this('event')->call('system-object-detail', $payload);

  //and determine the title of the table row
  //this will be used on the breadcrumbs and title for example
  $suggestion = $schema->getSuggestion($results);

  //pass all the relational data we collected
  $request
    ->setStage('relation', $relation->get())
    ->setStage('relation', 'schema', $schema->get())
    ->setStage('relation', 'schema', 'id', $data['id1'])
    ->setStage('relation', 'item', $results)
    ->setStage('relation', 'suggestion', $suggestion);

  //----------------------------//
  // 2. Render Template
  $route = sprintf(
    '/admin/spa/system/object/%s/detail/%s',
    $relation->getName(),
    $data['id2']
  );
  //now let the original search take over
  $this('http')->routeTo('get', $route, $request, $response);
});

/* Create Routes
-------------------------------- */

/**
 * Render the System Model Create Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/system/object/:schema/create', function (
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
  $data['schema'] = $schema->get();
  $data['schema']['fields'] = $schema->getFields();
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

  //if we only want the data
  if ($request->getStage('render') === 'false') {
    return $response->setResults($data);
  }

  //set title
  if (!isset($data['title'])) {
    $data['title'] = $this('lang')->translate(
      'Create %s',
      $data['schema']['singular']
    );
  }
  //set the action
  if (!isset($data['action'])) {
    $data['action'] = sprintf(
      '/admin/spa/system/object/%s/create',
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
  if (is_dir((string) $response->get('page', 'template_root') ?? '')) {
    $template = $response->get('page', 'template_root');
  }

  //render the body
  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->registerPartialFromFolder('form_body', 'html', true)
    ->registerPartialFromFolder('form_fieldset', 'html', true)
    ->registerPartialFromFolder('form_foot', 'html', true)
    ->registerPartialFromFolder('form_head', 'html', true)
    ->registerPartialFromFolder('form_information', 'html', true)
    ->registerPartialFromFolder('form_tabs', 'html', true)
    ->renderFromFolder('form', $data);

  //set content
  $response->setContent($body);
});

/**
 * Render the System Model Copy Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/system/object/:schema/create/:id', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //load the schema
  try {
    $schema = Schema::load($request->getStage('schema'));
  } catch (SystemException $e) {
    $response->setError(true, $e->getMessage());
    return $this('admin')->invalid($response);
  }

  //table_id, 1 for example
  $id = $request->getStage('id');
  $primary = $schema->getPrimaryName();
  $request->setStage($primary, $id);

  //get the original table row
  $this('event')->emit('system-object-detail', $request, $response);

  //can we view ?
  if ($response->isError()) {
    return $this('admin')->invalid($response);
  }

  //set the item
  $request->setStage('item', $response->getResults());
  //determine route
  $route = sprintf(
    '/admin/spa/system/object/%s/create',
    $request->getStage('schema')
  );

  //route to the original create route
  $this('http')->routeTo('get', $route, $request, $response);
});

/**
 * Process the System Model Create Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/admin/spa/system/object/:schema/create', function (
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

/**
 * Render the System Model Search Page Filtered by Relation
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/system/object/:schema1/:id/create/:schema2', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  $id = $request->getStage('id');

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

  //remove the data from stage
  //because we wont need it anymore
  $request
    ->removeStage('id')
    ->removeStage('schema1')
    ->removeStage('schema2');

  //----------------------------//
  // 2. Render Template
  //set the action
  if (!$request->hasStage('action')) {
    $request->setStage('action', sprintf(
      '/admin/spa/system/object/%s/%s/create/%s',
      $schema->get('name'),
      $id,
      $relation->get('name')
    ));
  }
  //after submit what then?
  if (!$request->hasStage('after')) {
    $request->setStage('after', 'reback');
  }

  //now let the original search take over
  $route = sprintf(
    '/admin/spa/system/object/%s/create',
    $relation->get('name')
  );
  $this('http')->routeTo('get', $route, $request, $response);
});

/**
 * Process the System Model Create Page Filtered by Relation
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/admin/spa/system/object/:schema1/:id/create/:schema2', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  $id = $request->getStage('id');

  //get schema and relation
  try {
    $schema = Schema::load($request->getStage('schema1'));
  } catch (SystemException $e) {
    return $response->setError(true, $e->getMessage());
  }

  $relation = $schema->getRelations(null, $request->getStage('schema2'));

  if (empty($relation)) {
    $response->setError(true, 'Cannot find a valid relation.');
    return $this('admin')->invalid($response);
  }

  //results will look like [profile_product => object]
  $table = array_key_first($relation);
  $relation = $relation[$table];

  //----------------------------//
  // 2. Process Request
  $route = sprintf(
    '/admin/spa/system/object/%s/create',
    $relation->get('name')
  );
  //now let the original create take over
  $this('http')->routeTo('post', $route, $request, $response);

  //----------------------------//
  // 3. Interpret Results
  //if there's an error
  if ($response->isError()) {
    return;
  }

  //so it must have been successful
  //lets link the tables now
  $primary1 = $schema->getPrimaryName();
  $primary2 = $relation->getPrimaryName();

  if ($primary1 == $primary2) {
    $primary1 = sprintf('%s_1', $primary1);
    $primary2 = sprintf('%s_2', $primary2);
  }

  //set the stage to link
  $payload = $request->clone(true);
  $payload
    ->setStage('schema1', $schema->getName())
    ->setStage('schema2', $relation->getName())
    ->setStage($primary1, $id)
    ->setStage($primary2, $response->getResults());

  //now link it
  $this('event')->call('system-relation-link', $payload);
});

/* Remove/Restore Routes
-------------------------------- */

/**
 * Process the System Model Remove
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/system/object/:schema/remove/:id', function (
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

  //table_id, 1 for example
  $id = $request->getStage('id');
  $primary = $schema->getPrimaryName();
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
  $item = $response->getResults();

  //set title
  if (!isset($data['title'])) {
    $data['title'] = $this('lang')->translate(
      'Remove %s',
      $schema->getSingular()
    );
  }
  //set message
  if (!isset($data['message'])) {
    $data['message'] = $this('lang')->translate(
      'Are you sure you want to remove %s ?',
      $schema->getSuggestion($item)
    );
  }
  //set the action
  if (!isset($data['action'])) {
    $data['action'] = sprintf(
      '/admin/spa/system/object/%s/remove/%s/confirmed',
      $schema->getName(),
      $item[$primary]
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

  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->renderFromFolder('confirm', $data);

  //set content
  $response->setContent($body);
});

/**
 * Process the System Model Remove
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/admin/spa/system/object/:schema/remove/:id/confirmed', function (
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

  //table_id, 1 for example
  $id = $request->getStage('id');
  $primary = $schema->getPrimaryName();
  $request->setStage($primary, $id);

  //----------------------------//
  // 2. Process Request
  $this('event')->emit('system-object-remove', $request, $response);
});

/**
 * Process the System Model Restore
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/system/object/:schema/restore/:id', function (
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

  //table_id, 1 for example
  $id = $request->getStage('id');
  $primary = $schema->getPrimaryName();
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
  $item = $response->getResults();

  //set title
  if (!isset($data['title'])) {
    $data['title'] = $this('lang')->translate(
      'Restore %s',
      $schema->getSingular()
    );
  }
  //set message
  if (!isset($data['message'])) {
    $data['message'] = $this('lang')->translate(
      'Are you sure you want to retore %s ?',
      $schema->getSuggestion($item)
    );
  }
  //set the action
  if (!isset($data['action'])) {
    $data['action'] = sprintf(
      '/admin/spa/system/object/%s/restore/%s/confirmed',
      $schema->getName(),
      $item[$primary]
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

  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->renderFromFolder('confirm', $data);

  //set content
  $response->setContent($body);
});

/**
 * Process the System Model Restore
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/admin/spa/system/object/:schema/restore/:id/confirmed', function (
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

  //table_id, 1 for example
  $id = $request->getStage('id');
  $primary = $schema->getPrimaryName();
  $request->setStage($primary, $id);

  //----------------------------//
  // 2. Process Request
  $this('event')->emit('system-object-restore', $request, $response);
});

/* Update Routes
-------------------------------- */

/**
 * Render the System Model Update Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/system/object/:schema/update/:id', function (
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

  //set dynamic keys to static
  $data['primary'] = $data['item'][$primary];
  $data['suggestion'] = $schema->getSuggestion($data['item']);

  //----------------------------//
  // 2. Render Template
  //set title
  if (!isset($data['title'])) {
    $data['title'] = $this('lang')->translate(
      'Update %s',
      $data['schema']['singular']
    );
  }
  //set the action
  if (!isset($data['action'])) {
    $data['action'] = sprintf(
      '/admin/spa/system/object/%s/update/%s',
      $data['schema']['name'],
      $data['item'][$primary]
    );
  }
  //after submit what then?
  if (!isset($data['after'])) {
    $data['after'] = 'reload';
  }

  $template = dirname(__DIR__) . '/template/object';
  if (is_dir($response->get('page', 'template_root') ?? '')) {
    $template = $response->get('page', 'template_root');
  }

  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->registerPartialFromFolder('form_body', 'html', true)
    ->registerPartialFromFolder('form_fieldset', 'html', true)
    ->registerPartialFromFolder('form_foot', 'html', true)
    ->registerPartialFromFolder('form_head', 'html', true)
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
$this('http')->post('/admin/spa/system/object/:schema/update/:id', function (
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

  //table_id, 1 for example
  $id = $request->getStage('id');
  $primary = $schema->getPrimaryName();
  $request->setStage($primary, $id);

  //----------------------------//
  // 2. Process Request
  $this('event')->emit('system-object-update', $request, $response);
});

/* Link Routes
-------------------------------- */

/**
 * Render the System Model Link Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/system/object/:schema1/:id/link/:schema2', function (
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

  $data['schema'] = $schema->get();
  $data['schema']['primary'] = $schema->getPrimaryName();
  $data['relation'] = $relation->get();
  $data['relation']['primary'] = $relation->getPrimaryName();

  $payload = $request->clone(true);

  $payload
    ->setStage('schema', $request->getStage('schema1'))
    ->setStage($data['schema']['primary'], $request->getStage('id'));

  //get the original table row
  $data['item'] = $this('event')->call('system-model-detail', $payload);

  //if we only want the data
  if ($request->getStage('render') === 'false') {
    return $response->setResults($data['item']);
  }

  //----------------------------//
  // 2. Render Template
  //set title
  if (!isset($data['title'])) {
    $data['title'] = $this('lang')->translate(
      'Link %s',
      $data['relation']['singular']
    );
  }
  //set the action
  if (!isset($data['action'])) {
    $data['action'] = sprintf(
      '/admin/spa/system/object/%s/%s/link/%s',
      $data['schema']['name'],
      $data['id'],
      $data['relation']['name']
    );
  }
  //after submit what then?
  if (!isset($data['after'])) {
    $data['after'] = 'reback';
  }

  $template = dirname(__DIR__) . '/template/object';
  if (is_dir($response->get('page', 'template_root') ?? '')) {
    $template = $response->get('page', 'template_root');
  }

  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->renderFromFolder('link', $data);

  //set content
  $response->setContent($body);
});

/**
 * Link model to model
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/admin/spa/system/object/:schema1/:id/link/:schema2', function (
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
    $response->setError(true, 'Cannot find a valid relation.');
    return $this('admin')->invalid($response);
  }

  //results will look like [profile_product => object]
  $table = array_key_first($relation);
  $relation = $relation[$table];

  //so it must have been successful
  //lets link the tables now
  $primary1 = $schema->getPrimaryName();
  $primary2 = $relation->getPrimaryName();

  if ($primary1 == $primary2) {
    $primary1 = sprintf('%s_1', $primary1);
    $primary2 = sprintf('%s_2', $primary2);
  }

  $id1 = $request->getStage('id');
  $id2 = $request->getStage($primary2);

  //----------------------------//
  // 2. Process Request
  //set the stage to link
  $payload = $request->clone(true);
  $payload
    ->setStage('schema1', $schema->getName())
    ->setStage('schema2', $relation->getName())
    ->setStage($primary1, $id1)
    ->setStage($primary2, $id2);

  //now link it
  try {
    $this('event')->emit('system-relation-link', $payload, $response);
  } catch(Throwable $e) {
    //if it's a store error, it's probably because it's already linked
  }

  $response->setError(false);
});

/* Unlink Routes
-------------------------------- */

/**
 * Unlink model from model
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/system/object/:schema1/:id1/unlink/:schema2/:id2', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
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

  //lets link the tables now
  $primary1 = $schema->getPrimaryName();
  $primary2 = $relation->getPrimaryName();

  $id1 = $request->getStage('id1');
  $id2 = $request->getStage('id2');

  $payload = $request->clone(true);
  $payload
    ->setStage('schema', $schema->get('name'))
    ->setStage($primary1, $id1);

  $object1 = $this('event')->call('system-object-detail', $payload);

  if (!$object1) {
    $response->setError(true, 'Not Found');
    return $this('admin')->invalid($response);
  }

  $payload = $request->clone(true);
  $payload
    ->setStage('schema', $relation->get('name'))
    ->setStage($primary2, $id2);

  $object2 = $this('event')->call('system-object-detail', $payload);

  if (!$object2) {
    $response->setError(true, 'Not Found');
    return $this('admin')->invalid($response);
  }

  //set title
  if (!isset($data['title'])) {
    $data['title'] = $this('lang')->translate(
      'Unlink %s',
      $relation->getSingular()
    );
  }
  //set message
  if (!isset($data['message'])) {
    $data['message'] = $this('lang')->translate(
      'Are you sure you want to unlink %s from %s ?',
      $relation->getSuggestion($object2),
      $schema->getSuggestion($object1)
    );
  }
  //set the action
  if (!isset($data['action'])) {
    $data['action'] = sprintf(
      '/admin/spa/system/object/%s/%s/unlink/%s/%s/confirmed',
      $schema->getName(),
      $id1,
      $relation->getName(),
      $id2
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

  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->renderFromFolder('confirm', $data);

  //set content
  $response->setContent($body);
});

/**
 * Unlink model from model
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/admin/spa/system/object/:schema1/:id1/unlink/:schema2/:id2/confirmed', function (
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
    $response->setError(true, 'Cannot find a valid relation.');
    return $this('admin')->invalid($response);
  }

  //results will look like [profile_product => object]
  $table = array_key_first($relation);
  $relation = $relation[$table];

  //lets link the tables now
  $primary1 = $schema->getPrimaryName();
  $primary2 = $relation->getPrimaryName();

  if ($primary1 == $primary2) {
    $primary1 = sprintf('%s_1', $primary1);
    $primary2 = sprintf('%s_2', $primary2);
  }

  $id1 = $request->getStage('id1');
  $id2 = $request->getStage('id2');

  //----------------------------//
  // 2. Process Request
  //set the stage to link
  $payload = $request->clone(true);
  $payload
    ->setStage('schema1', $schema->getName())
    ->setStage('schema2', $relation->getName())
    ->setStage($primary1, $id1)
    ->setStage($primary2, $id2);

  //now unlink it
  try {
    $this('event')->emit('system-relation-unlink', $payload, $response);
  } catch(Throwable $e) {
    return $response->setError(true, $e->getMessage());
  }
});
