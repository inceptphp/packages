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
 * $ incept inceptphp/packages/handlebars ...
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/handlebars', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $event = 'help';

  if($request->hasStage(0)) {
    $event = $request->getStage(0);
    $request->removeStage(0);
  }

  if($request->hasStage()) {
    $data = [];
    $stage = $request->getStage();
    foreach($stage as $key => $value) {
      if(!is_numeric($key)) {
        $data[$key] = $value;
      } else {
        $data[$key - 1] = $value;
      }

      $request->removeStage($key);
    }

    $request->setStage($data);
  }

  $name = 'inceptphp/packages/handlebars';
  $event = sprintf('%s-%s', $name, $event);

  $this('event')->emit($event, $request, $response);
});

/**
 * $ incept inceptphp/packages/handlebars help
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/handlebars-help', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $this('terminal')
    ->warning('Admin Commands:')

    ->output(PHP_EOL)

    ->success('incept inceptphp/packages/handlebars install')
    ->info(' Installs this package')

    ->output(PHP_EOL)

    ->success('incept inceptphp/packages/handlebars update')
    ->info(' Updates this package')

    ->output(PHP_EOL)

    ->success('incept inceptphp/packages/handlebars uninstall')
    ->info(' Removes this package')

    ->output(PHP_EOL);
});

/**
 * $ incept inceptphp/packages/handlebars install
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/handlebars-install', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $request->setStage('name', 'inceptphp/packages/handlebars');
  //just do the default installer
  $this('event')->emit('inceptphp/packages-install', $request, $response);
});

/**
 * $ incept inceptphp/packages/handlebars update
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/handlebars-update', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $request->setStage('name', 'inceptphp/packages/handlebars');
  //just do the default installer
  $this('event')->emit('inceptphp/packages-update', $request, $response);
});

/**
 * $ incept inceptphp/packages/handlebars uninstall
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/handlebars-uninstall', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $request->setStage('name', 'inceptphp/packages/handlebars');
  //just do the default installer
  $this('event')->emit('inceptphp/packages-uninstall', $request, $response);
});
