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
 * 404 and 500 page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 * @param Throwable $error
 */
$this->error(function (
  RequestInterface $request,
  ResponseInterface $response,
  Throwable $error
) {

  $path = $request->getPath('string');
  //if not an admin path
  if ($path !== '/admin' && strpos($path, '/admin/') !== 0) {
    return;
  }

  //if it was a call for an actual file
  if (preg_match('/\.[a-zA-Z0-9]{1,4}$/', $path)) {
    return;
  }

  //if this is not an html page
  $type = $response->getHeaders('Content-Type');
  if (strpos($type, 'html') === false) {
    //don't make it pretty
    return;
  }

  //get the code
  $code = $response->getCode();
  //set the template root
  $template = __DIR__ . '/template';

  if ($code === 404) {
    $body = $this('handlebars')
      ->setTemplateFolder($template)
      ->renderFromFolder('404');

    //set content
    $response
      ->setPage('title', $this('lang')->translate('Oops...'))
      ->setPage('class', 'page-404 page-error')
      ->setContent($body);

    //render page
    $this('admin')->render($request, $response);

    return true;
  }

  //get config settings
  $config = $this('config')->get('settings');

  //if no environment
  if (!isset($config['environment'])
    //or the environment is not production
    || $config['environment'] !== 'production'
    //or it's not a 500 error
    || $code !== 500
  ) {
    //don't make it pretty
    return;
  }

  //okay make it pretty...
  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->renderFromFolder('500');

  //set content
  $response
    ->setPage('title', $this('lang')->translate('Oops...'))
    ->setPage('class', 'page-500 page-error')
    ->setContent($body);

  //render page
  $this('admin')->render($request, $response);

  if (!isset($config['error_email'])
    || $config['error_email'] === '<EMAIL ADDRESS>'
  ) {
    return true;
  }

  //send mail eventually
  $this('http')->postprocess(function() {
    //send mail
    $this('event')->call('email-send', [
      'to' => [
        [
          'address' => $config['error_email']
        ]
      ],
      'from' => [
        'name' => $service['name'],
        'address' => $service['user']
      ],
      'subject' => sprintf('%s - Error', $config['name']),
      'plain' => sprintf(
        "%s thrown: %s\n%s(%s)\n\n%s",
        get_class($error),
        $error->getMessage(),
        $error->getFile(),
        $error->getLine(),
        $error->getTraceAsString()
      )
    ]);
  });

  return true;
});
