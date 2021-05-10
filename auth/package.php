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
 * $ incept inceptphp/packages/auth ...
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/auth', function (
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

  $name = 'inceptphp/packages/auth';
  $event = sprintf('%s-%s', $name, $event);

  $this('event')->emit($event, $request, $response);
});

/**
 * $ incept inceptphp/packages/auth help
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/auth-help', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $this('terminal')
    ->warning('Profile Commands:')

    ->output(PHP_EOL)

    ->success('incept inceptphp/packages/auth install')
    ->info(' Installs this package')

    ->output(PHP_EOL)

    ->success('incept inceptphp/packages/auth update')
    ->info(' Updates this package')

    ->output(PHP_EOL)

    ->success('incept inceptphp/packages/auth uninstall')
    ->info(' Removes this package')

    ->output(PHP_EOL)

    ->success('incept inceptphp/packages/auth populate')
    ->info(' Populates the first row with the admin account. (admin/admin)')

    ->output(PHP_EOL);
});

/**
 * $ incept inceptphp/packages/auth install
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/auth-install', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $request
    ->setStage('name', 'inceptphp/packages/auth')
    ->setStage('install', __DIR__ . '/install');
  //just do the default installer
  $this('event')->emit('inceptphp/packages-install', $request, $response);

  $response->setResults('recommended', 'auth', 'bin/incept inceptphp/packages/auth populate');
});

/**
 * $ incept inceptphp/packages/auth update
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/auth-update', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $request
    ->setStage('name', 'inceptphp/packages/auth')
    ->setStage('install', __DIR__ . '/install');
  //just do the default installer
  $this('event')->emit('inceptphp/packages-update', $request, $response);
});

/**
 * $ incept inceptphp/packages/auth uninstall
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/auth-uninstall', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $request
    ->setStage('name', 'inceptphp/packages/auth')
    ->setStage('schema', __DIR__ . '/schema');
  //just do the default installer
  $this('event')->emit('inceptphp/packages-uninstall', $request, $response);
});

/**
 * $ incept inceptphp/packages/auth populate
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/auth-populate', function (
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

      $emitter->call('auth-create', $payload);
    }
  }

  $terminal = $this('terminal');
  $terminal->info('Created Login');
  $terminal->info(' - Username: admin');
  $terminal->info(' - Password: admin');
});
