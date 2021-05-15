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
 * Render Block Create Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/system/object/block/create', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $root = dirname(__DIR__);
  $this('handlebars')
    ->registerPartialFromFile('form_body', sprintf(
      '/%s/template/block/_body.html',
      $root
    ), true)
    ->registerPartialFromFile('form_event', sprintf(
      '/%s/template/block/_event.html',
      $root
    ), true)
    ->registerPartialFromFile('form_information', sprintf(
      '/%s/template/block/_information.html',
      $root
    ), true)
    ->registerPartialFromFile('form_tabs', sprintf(
      '/%s/template/block/_tabs.html',
      $root
    ), true)
    ->registerPartialFromFile('form_template', sprintf(
      '/%s/template/block/_template.html',
      $root
    ), true);
}, 10);

/**
 * Render Block Update Page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/system/object/block/update/:page_id', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $root = dirname(__DIR__);
  $this('handlebars')
    ->registerPartialFromFile('form_body', sprintf(
      '/%s/template/block/_body.html',
      $root
    ), true)
    ->registerPartialFromFile('form_event', sprintf(
      '/%s/template/block/_event.html',
      $root
    ), true)
    ->registerPartialFromFile('form_information', sprintf(
      '/%s/template/block/_information.html',
      $root
    ), true)
    ->registerPartialFromFile('form_tabs', sprintf(
      '/%s/template/block/_tabs.html',
      $root
    ), true)
    ->registerPartialFromFile('form_template', sprintf(
      '/%s/template/block/_template.html',
      $root
    ), true);
}, 10);
