<?php //-->

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

/**
 * Render the JSON files we have
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/json/:name.json', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $name = $request->getStage('name');
  $file = sprintf('%s/assets/json/%s.json', dirname(__DIR__), $name);

  if (!file_exists($file)) {
    return;
  }

  $response->addHeader('Content-Type', 'text/json');
  $response->setContent(file_get_contents($file));
});

/**
 * Render Admin JS
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/scripts/admin.js', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $body = $this('handlebars')
    ->setTemplateFolder(dirname(__DIR__) . '/assets')
    ->registerPartialFromFolder('script_app', 'js', true)
    ->registerPartialFromFolder('script_fields', 'js', true)
    ->registerPartialFromFolder('script_form', 'js', true)
    ->registerPartialFromFolder('script_misc', 'js', true)
    ->registerPartialFromFolder('script_jquery', 'js', true)
    ->registerPartialFromFolder('script_search', 'js', true)
    ->renderFromFolder('admin', [], 'js');

  $response->addHeader('Content-Type', 'text/javascript');
  $response->setContent($body);
});

/**
 * Render Admin CSS
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/styles/admin.css', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $body = $this('handlebars')
    ->setTemplateFolder(dirname(__DIR__) . '/assets')
    ->registerPartialFromFolder('style_auth', 'css', true)
    ->registerPartialFromFolder('style_components', 'css', true)
    ->registerPartialFromFolder('style_error', 'css', true)
    ->registerPartialFromFolder('style_fields', 'css', true)
    ->registerPartialFromFolder('style_form', 'css', true)
    ->registerPartialFromFolder('style_layout', 'css', true)
    ->registerPartialFromFolder('style_reset', 'css', true)
    ->registerPartialFromFolder('style_search', 'css', true)
    ->registerPartialFromFolder('style_theme', 'css', true)
    ->registerPartialFromFolder('style_twbs', 'css', true)
    ->registerPartialFromFolder('style_view', 'css', true)
    ->renderFromFolder('admin', [], 'css');

  $response->addHeader('Content-Type', 'text/css');
  $response->setContent($body);
});
