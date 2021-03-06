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
  $root = dirname(__DIR__);
  $this('handlebars')
    ->registerPartialFromFile('form_body', sprintf(
      '/%s/template/page/_body.html',
      $root
    ), true)
    ->registerPartialFromFile('form_event', sprintf(
      '/%s/template/page/_event.html',
      $root
    ), true)
    ->registerPartialFromFile('form_information', sprintf(
      '/%s/template/page/_information.html',
      $root
    ), true)
    ->registerPartialFromFile('form_seo', sprintf(
      '/%s/template/page/_seo.html',
      $root
    ), true)
    ->registerPartialFromFile('form_tabs', sprintf(
      '/%s/template/page/_tabs.html',
      $root
    ), true)
    ->registerPartialFromFile('form_template', sprintf(
      '/%s/template/page/_template.html',
      $root
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
  $root = dirname(__DIR__);
  $this('handlebars')
    ->registerPartialFromFile('form_body', sprintf(
      '/%s/template/page/_body.html',
      $root
    ), true)
    ->registerPartialFromFile('form_event', sprintf(
      '/%s/template/page/_event.html',
      $root
    ), true)
    ->registerPartialFromFile('form_information', sprintf(
      '/%s/template/page/_information.html',
      $root
    ), true)
    ->registerPartialFromFile('form_seo', sprintf(
      '/%s/template/page/_seo.html',
      $root
    ), true)
    ->registerPartialFromFile('form_tabs', sprintf(
      '/%s/template/page/_tabs.html',
      $root
    ), true)
    ->registerPartialFromFile('form_template', sprintf(
      '/%s/template/page/_template.html',
      $root
    ), true);
}, 10);
