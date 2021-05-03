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
use Incept\Framework\Fieldset;

/**
 * Render the REST Documentaion Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/developer/docs', function (
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
  //----------------------------//
  // 3. Render Template
  $template = dirname(__DIR__) . '/template';
  if (is_dir($response->get('page', 'template_root'))) {
    $template = $response->get('page', 'template_root');
  }

  $body = $handlebars
    ->setTemplateFolder($template)
    ->renderFromFolder('docs');

  //set content
  $response
    ->set('page', 'title', $lang->translate('API Documentation'))
    ->set('page', 'class', 'page-developer-docs page-developer')
    ->setContent($body);

  //if we only want the body
  if ($request->getStage('render') === 'body') {
    return;
  }

  //render page
  $emitter->emit('render-page', $request, $response);
});

/**
 * Render the REST Documentaion Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/developer/docs/calls', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Declare Packages
  $http = $this('http');
  $lang = $this('lang');
  $emitter = $this('event');
  $handlebars = $this('handlebars');

  //----------------------------//
  // 2. Setup Overrides
  //determine redirect
  $redirect = $request->getStage('redirect_uri') ?? '/';

  //----------------------------//
  // 3. Prepare Data
  $emitter->emit('system-collection-rest-search', $request, $response);

  //if no rendering
  if ($request->getStage('render') === 'false') {
    return;
  }

  //if there's an error
  if ($response->isError()) {
    $response->setSession('flash', [
      'message' => $response->getMessage(),
      'type' => 'error'
    ]);

    return $http->redirect($redirect);
  }

  $data = $response->getResults();
  $data['schema'] = Schema::load('rest')->get();

  //----------------------------//
  // 4. Render Template
  $template = dirname(__DIR__) . '/template';
  if (is_dir($response->get('page', 'template_root'))) {
    $template = $response->get('page', 'template_root');
  }

  $body = $handlebars
    ->setTemplateFolder($template)
    ->renderFromFolder('docs/calls', $data);

  //set content
  $response
    ->set('page', 'title', $lang->translate('REST Calls - API Documentation'))
    ->set('page', 'class', 'page-developer-docs page-developer')
    ->setContent($body);

  //if we only want the body
  if ($request->getStage('render') === 'body') {
    return;
  }

  //render page
  $emitter->emit('render-page', $request, $response);
});

/**
 * Render the REST Documentaion Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/developer/docs/calls/:rest_id', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Declare Packages
  $http = $this('http');
  $lang = $this('lang');
  $emitter = $this('event');
  $handlebars = $this('handlebars');

  //----------------------------//
  // 2. Setup Overrides
  //determine redirect
  $redirect = $request->getStage('redirect_uri') ?? '/';

  //----------------------------//
  // 3. Prepare Data
  $emitter->emit('system-object-rest-detail', $request, $response);

  //if no rendering
  if ($request->getStage('render') === 'false') {
    return;
  }

  //if there's an error
  if ($response->isError()) {
    $response->setSession('flash', [
      'message' => $response->getMessage(),
      'type' => 'error'
    ]);

    return $http->redirect($redirect);
  }

  $data = $response->getResults();
  $data['schema'] = Schema::load('rest')->get();

  $data['title'] = $lang->translate(
    '%s - API Documentation',
    $response->getResults('rest_title')
  );

  //----------------------------//
  // 4. Render Template
  $template = dirname(__DIR__) . '/template';
  if (is_dir($response->get('page', 'template_root'))) {
    $template = $response->get('page', 'template_root');
  }

  $body = $handlebars
    ->setTemplateFolder($template)
    ->renderFromFolder('docs/call', $data);

  //set content
  $response
    ->set('page', 'title', $data['title'])
    ->set('page', 'class', 'page-developer-docs page-developer')
    ->setContent($body);

  //if we only want the body
  if ($request->getStage('render') === 'body') {
    return;
  }

  //render page
  $emitter->emit('render-page', $request, $response);
});

/**
 * Render the Scope Documentaion Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/developer/docs/scopes', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Declare Packages
  $http = $this('http');
  $lang = $this('lang');
  $emitter = $this('event');
  $handlebars = $this('handlebars');

  //----------------------------//
  // 2. Setup Overrides
  //determine redirect
  $redirect = $request->getStage('redirect_uri') ?? '/';

  //----------------------------//
  // 3. Prepare Data
  $emitter->emit('system-collection-scope-search', $request, $response);

  //if no rendering
  if ($request->getStage('render') === 'false') {
    return;
  }

  //if there's an error
  if ($response->isError()) {
    $response->setSession('flash', [
      'message' => $response->getMessage(),
      'type' => 'error'
    ]);

    return $http->redirect($redirect);
  }

  $data = $response->getResults();
  $data['schema'] = Schema::load('scope')->get();
  $data['title'] = $lang->translate(
    '%s - API Documentation',
    $data['schema']['plural']
  );

  //----------------------------//
  // 4. Render Template
  $template = dirname(__DIR__) . '/template';
  if (is_dir($response->get('page', 'template_root'))) {
    $template = $response->get('page', 'template_root');
  }

  $body = $handlebars
    ->setTemplateFolder($template)
    ->renderFromFolder('docs/scopes', $data);

  //set content
  $response
    ->set('page', 'title', $data['title'])
    ->set('page', 'class', 'page-developer-docs page-developer')
    ->setContent($body);

  //if we only want the body
  if ($request->getStage('render') === 'body') {
    return;
  }

  //render page
  $emitter->emit('render-page', $request, $response);
});

/**
 * Render the Scope Documentaion Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/developer/docs/scopes/:scope_slug', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Declare Packages
  $http = $this('http');
  $lang = $this('lang');
  $emitter = $this('event');
  $handlebars = $this('handlebars');

  //----------------------------//
  // 2. Setup Overrides
  //determine redirect
  $redirect = $request->getStage('redirect_uri') ?? '/';

  //----------------------------//
  // 3. Prepare Data
  $emitter->emit('system-object-scope-detail', $request, $response);

  //if no rendering
  if ($request->getStage('render') === 'false') {
    return;
  }

  //if there's an error
  if ($response->isError()) {
    $response->setSession('flash', [
      'message' => $response->getMessage(),
      'type' => 'error'
    ]);

    return $http->redirect($redirect);
  }

  $data = $response->getResults();
  $data['schema'] = Schema::load('scope')->get();
  $data['title'] = $lang->translate(
    '%s - API Documentation',
    $response->getResults('scope_name')
  );

  //----------------------------//
  // 4. Render Template
  $template = dirname(__DIR__) . '/template';
  if (is_dir($response->get('page', 'template_root'))) {
    $template = $response->get('page', 'template_root');
  }

  $body = $handlebars
    ->setTemplateFolder($template)
    ->renderFromFolder('docs/scope', $data);

  //set content
  $response
    ->set('page', 'title', $data['title'])
    ->set('page', 'class', 'page-developer-docs page-developer')
    ->setContent($body);

  //if we only want the body
  if ($request->getStage('render') === 'body') {
    return;
  }

  //render page
  $emitter->emit('render-page', $request, $response);
});

/**
 * Render the Webhook Documentaion Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/developer/docs/webhooks', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Declare Packages
  $http = $this('http');
  $lang = $this('lang');
  $emitter = $this('event');
  $handlebars = $this('handlebars');

  //----------------------------//
  // 2. Setup Overrides
  //determine redirect
  $redirect = $request->getStage('redirect_uri') ?? '/';

  //----------------------------//
  // 3. Prepare Data
  $emitter->emit('system-collection-webhook-search', $request, $response);

  //if no rendering
  if ($request->getStage('render') === 'false') {
    return;
  }

  //if there's an error
  if ($response->isError()) {
    $response->setSession('flash', [
      'message' => $response->getMessage(),
      'type' => 'error'
    ]);

    return $http->redirect($redirect);
  }

  $data = $response->getResults();
  $data['schema'] = Schema::load('webhook')->get();
  $data['title'] = $lang->translate(
    '%s - API Documentation',
    $data['schema']['plural']
  );

  //----------------------------//
  // 4. Render Template
  $template = dirname(__DIR__) . '/template';
  if (is_dir($response->get('page', 'template_root'))) {
    $template = $response->get('page', 'template_root');
  }

  $body = $handlebars
    ->setTemplateFolder($template)
    ->renderFromFolder('docs/webhooks', $data);

  //set content
  $response
    ->set('page', 'title', $data['title'])
    ->set('page', 'class', 'page-developer-docs page-developer')
    ->setContent($body);

  //if we only want the body
  if ($request->getStage('render') === 'body') {
    return;
  }

  //render page
  $emitter->emit('render-page', $request, $response);
});

/**
 * Render the Webhook Documentaion Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/developer/docs/webhooks/:webhook_id', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Declare Packages
  $http = $this('http');
  $lang = $this('lang');
  $emitter = $this('event');
  $handlebars = $this('handlebars');

  //----------------------------//
  // 2. Setup Overrides
  //determine redirect
  $redirect = $request->getStage('redirect_uri') ?? '/';

  //----------------------------//
  // 3. Prepare Data
  $emitter->emit('system-object-webhook-detail', $request, $response);

  //if no rendering
  if ($request->getStage('render') === 'false') {
    return;
  }

  //if there's an error
  if ($response->isError()) {
    $response->setSession('flash', [
      'message' => $response->getMessage(),
      'type' => 'error'
    ]);

    return $http->redirect($redirect);
  }

  $data = $response->getResults();
  $data['schema'] = Schema::load('webhook')->get();
  $data['title'] = $lang->translate(
    '%s - API Documentation',
    $response->getResults('webhook_title')
  );

  //----------------------------//
  // 4. Render Template
  $template = dirname(__DIR__) . '/template';
  if (is_dir($response->get('page', 'template_root'))) {
    $template = $response->get('page', 'template_root');
  }

  $body = $handlebars
    ->setTemplateFolder($template)
    ->renderFromFolder('docs/webhook', $data);

  //set content
  $response
    ->set('page', 'title', $data['title'])
    ->set('page', 'class', 'page-developer-docs page-developer')
    ->setContent($body);

  //if we only want the body
  if ($request->getStage('render') === 'body') {
    return;
  }

  //render page
  $emitter->emit('render-page', $request, $response);
});

/**
 * Render the REST Documentaion Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/developer/docs/schema/:name', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Declare Packages
  $lang = $this('lang');
  $emitter = $this('event');
  $handlebars = $this('handlebars');

  //----------------------------//
  // 3. Prepare Data
  try { //try to load the schema
    $schema = Schema::load($request->getStage('name'));
  } catch (Throwable $e) {
    //let it 404
    return;
  }

  $data['schema'] = $schema->get();
  $response->setResults($data['schema']);

  //find all the fieldsets
  $data['fieldsets'] = [];
  foreach ($data['schema']['fields'] as $field) {
    if ($field['field']['type'] !== 'fieldset') {
      continue;
    }

    try { //try to load the schema
      $data['fieldsets'][$field['field']['parameters']] = Fieldset::load(
        $field['field']['parameters']
      )->get();
    } catch (Throwable $e) {}
  }

  //if no rendering
  if ($request->getStage('render') === 'false') {
    return;
  }

  $data['title'] = $lang->translate(
    '%s - API Documentation',
    $data['schema']['singular']
  );

  //----------------------------//
  // 4. Render Template
  $template = dirname(__DIR__) . '/template';
  if (is_dir($response->get('page', 'template_root'))) {
    $template = $response->get('page', 'template_root');
  }

  $body = $handlebars
    ->setTemplateFolder($template)
    ->registerHelper('sample', include dirname(__DIR__) . '/helpers/sample.php')
    ->registerPartialFromFolder('docs_schema_fields', 'html', true)
    ->registerPartialFromFolder('docs_schema_detail', 'html', true)
    ->registerPartialFromFolder('docs_schema_validation', 'html', true)
    ->registerPartialFromFolder('docs_schema_type', 'html', true)
    ->registerPartialFromFolder('docs_schema_minrequest', 'html', true)
    ->registerPartialFromFolder('docs_schema_maxrequest', 'html', true)
    ->registerPartialFromFolder('docs_schema_parameter', 'html', true)
    ->registerPartialFromFolder('docs_schema_response', 'html', true)
    ->renderFromFolder('docs/schema', $data);

  //set content
  $response
    ->set('page', 'title', $data['title'])
    ->set('page', 'class', 'page-developer-docs page-developer')
    ->setContent($body);

  //if we only want the body
  if ($request->getStage('render') === 'body') {
    return;
  }

  //render page
  $emitter->emit('render-page', $request, $response);
});
