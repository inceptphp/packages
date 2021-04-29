<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

/**
 * Renders search
 *
 * @param *RequestInterface  $request
 * @param *ResponseInterface $response
 */
$this('http')->get('/admin/system/object/role/search', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //we want to change the links up to use the role routes
  if (!$response->exists('page', 'template_root')) {
    $response->set('page', 'template_root', __DIR__ . '/template');
  }
}, 10);

/**
 * Renders a create form
 *
 * @param *RequestInterface  $request
 * @param *ResponseInterface $response
 */
$this('http')->get('/admin/spa/system/object/role/create', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //we want to change the links up to use the role routes
  if (!$response->exists('page', 'template_root')) {
    $response->set('page', 'template_root', __DIR__ . '/template');
  }

  $this('handlebars')
    ->registerPartialFromFile('form_permissions', sprintf(
      '/%s/template/form/_permissions.html', __DIR__
    ))
    ->registerPartialFromFile('form_menu', sprintf(
      '/%s/template/form/_menu.html', __DIR__
    ))
    ->registerPartialFromFile('form_menu_item', sprintf(
      '/%s/template/form/menu/_item.html', __DIR__
    ))
    ->registerPartialFromFile('form_menu_input', sprintf(
      '/%s/template/form/menu/_input.html', __DIR__
    ));
}, 10);

/**
 * Renders an update form
 *
 * @param *RequestInterface  $request
 * @param *ResponseInterface $response
 */
$this('http')->get('/admin/spa/system/object/role/update/:role_id', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //we want to change the links up to use the role routes
  if (!$response->exists('page', 'template_root')) {
    $response->set('page', 'template_root', __DIR__ . '/template');
  }

  $this('handlebars')
    ->registerPartialFromFile('form_permissions', sprintf(
      '/%s/template/form/_permissions.html', __DIR__
    ))
    ->registerPartialFromFile('form_menu', sprintf(
      '/%s/template/form/_menu.html', __DIR__
    ))
    ->registerPartialFromFile('form_menu_item', sprintf(
      '/%s/template/form/menu/_item.html', __DIR__
    ))
    ->registerPartialFromFile('form_menu_input', sprintf(
      '/%s/template/form/menu/_input.html', __DIR__
    ));
}, 10);

/**
 * Processes a create form
 *
 * @param *RequestInterface  $request
 * @param *ResponseInterface $response
 */
$this('http')->post('/admin/spa/system/object/role/create', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //flatten the JSON fields
  $permissions = $request->getStage('role_permissions');
  $adminMenu = $request->getStage('role_admin_menu');

  $request->setStage(
    'role_permissions',
    json_encode($permissions, JSON_PRETTY_PRINT)
  );

  $request->setStage(
    'role_admin_menu',
    json_encode($adminMenu, JSON_PRETTY_PRINT)
  );
}, 10);

/**
 * Processes an update form
 *
 * @param *RequestInterface  $request
 * @param *ResponseInterface $response
 */
$this('http')->post('/admin/spa/system/object/role/update/:role_id', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //flatten the JSON fields
  $permissions = $request->getStage('role_permissions');
  $adminMenu = $request->getStage('role_admin_menu');

  $request->setStage(
    'role_permissions',
    json_encode($permissions, JSON_PRETTY_PRINT)
  );

  $request->setStage(
    'role_admin_menu',
    json_encode($adminMenu, JSON_PRETTY_PRINT)
  );
}, 10);
