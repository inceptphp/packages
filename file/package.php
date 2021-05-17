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
 * $ incept inceptphp/packages/file ...
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/file', function (
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

  $name = 'inceptphp/packages/file';
  $event = sprintf('%s-%s', $name, $event);

  $this('event')->emit($event, $request, $response);
});

/**
 * $ incept inceptphp/packages/file help
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/file-help', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $this('terminal')
    ->warning('Admin Commands:')

    ->output(PHP_EOL)

    ->success('incept inceptphp/packages/file install')
    ->info(' Installs this package')

    ->output(PHP_EOL)

    ->success('incept inceptphp/packages/file update')
    ->info(' Updates this package')

    ->output(PHP_EOL)

    ->success('incept inceptphp/packages/file populate')
    ->info(' Populates a dummy file.')

    ->output(PHP_EOL)

    ->success('incept inceptphp/packages/file uninstall')
    ->info(' Removes this package')

    ->output(PHP_EOL);
});

/**
 * $ incept inceptphp/packages/file install
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/file-install', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $request
    ->setStage('name', 'inceptphp/packages/file')
    ->setStage('install', __DIR__ . '/install');
  //just do the default installer
  $this('event')->emit('inceptphp/packages-install', $request, $response);

  $response->setResults('recommended', 'file', 'bin/incept inceptphp/packages/file populate');
});

/**
 * $ incept inceptphp/packages/file update
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/file-update', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $request->setStage('name', 'inceptphp/packages/file');
  //just do the default installer
  $this('event')->emit('inceptphp/packages-update', $request, $response);
});

/**
 * $ incept inceptphp/packages/file uninstall
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/file-uninstall', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $request->setStage('name', 'inceptphp/packages/file');
  //just do the default installer
  $this('event')->emit('inceptphp/packages-uninstall', $request, $response);
});

/**
 * $ incept inceptphp/packages/auth populate
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/file-populate', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //scan through each file
  foreach (scandir(__DIR__ . '/schema') as $file) {
    //if it's not a php file
    if(substr($file, -4) !== '.php') {
      //skip
      continue;
    }

    //get the schema data
    $data = include sprintf('%s/schema/%s', __DIR__, $file);

    //if no name
    if (!isset($data['name'], $data['fixtures'])
      || !is_array($data['fixtures'])
    ) {
      //skip
      continue;
    }

    //get emitter
    $emitter = $this('event');
    foreach($data['fixtures'] as $fixture) {
      $payload = $request
        ->clone(true)
        ->setStage($fixture)
        ->setStage('schema', $data['name']);

      $emitter->call('system-object-create', $payload);
    }
  }
});
