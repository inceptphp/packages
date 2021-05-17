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
  $this('handlebars')
    ->registerPartialFromFile('form_body', sprintf(
      '/%s/template/form/_body.html',
      __DIR__
    ), true)
    ->registerPartialFromFile('form_tabs', sprintf(
      '/%s/template/form/_tabs.html',
      __DIR__
    ), true)
    ->registerPartialFromFile('form_information', sprintf(
      '/%s/template/form/_information.html',
      __DIR__
    ), true)
    ->registerPartialFromFile('form_permissions', sprintf(
      '/%s/template/form/_permissions.html',
      __DIR__
    ), true)
    ->registerPartialFromFile('form_menu', sprintf(
      '/%s/template/form/_menu.html',
      __DIR__
    ), true)
    ->registerPartialFromFile('form_menu_item', sprintf(
      '/%s/template/form/menu/_item.html',
      __DIR__
    ), true)
    ->registerPartialFromFile('form_menu_input', sprintf(
      '/%s/template/form/menu/_input.html',
      __DIR__
    ), true);
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
  $this('handlebars')
    ->registerPartialFromFile('form_body', sprintf(
      '/%s/template/form/_body.html',
      __DIR__
    ), true)
    ->registerPartialFromFile('form_tabs', sprintf(
      '/%s/template/form/_tabs.html',
      __DIR__
    ), true)
    ->registerPartialFromFile('form_information', sprintf(
      '/%s/template/form/_information.html',
      __DIR__
    ), true)
    ->registerPartialFromFile('form_permissions', sprintf(
      '/%s/template/form/_permissions.html',
      __DIR__
    ), true)
    ->registerPartialFromFile('form_menu', sprintf(
      '/%s/template/form/_menu.html',
      __DIR__
    ), true)
    ->registerPartialFromFile('form_menu_item', sprintf(
      '/%s/template/form/menu/_item.html',
      __DIR__
    ), true)
    ->registerPartialFromFile('form_menu_input', sprintf(
      '/%s/template/form/menu/_input.html',
      __DIR__
    ), true);
}, 10);
