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
 * Render Page Create Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/system/object/page/create', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $this('handlebars')->registerPartialFromFile('form_information', sprintf(
    '/%s/template/form/_information.html',
    dirname(__DIR__)
  ), true);
}, 10);

/**
 * Render Page Update Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/system/object/page/update/:page_id', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $this('handlebars')->registerPartialFromFile('form_information', sprintf(
    '/%s/template/form/_information.html',
    dirname(__DIR__)
  ), true);
}, 10);
