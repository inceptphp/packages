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
 * $ incept inceptphp/packages/profile ...
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/profile', function (
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

  $name = 'inceptphp/packages/profile';
  $event = sprintf('%s-%s', $name, $event);

  $this('event')->emit($event, $request, $response);
});

/**
 * $ incept inceptphp/packages/profile help
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/profile-help', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $this('terminal')
    ->warning('Profile Commands:')

    ->output(PHP_EOL)

    ->success('incept inceptphp/packages/profile install')
    ->info(' Installs this package')

    ->output(PHP_EOL)

    ->success('incept inceptphp/packages/profile update')
    ->info(' Updates this package')

    ->output(PHP_EOL)

    ->success('incept inceptphp/packages/profile uninstall')
    ->info(' Removes this package')

    ->output(PHP_EOL);
});

/**
 * $ incept inceptphp/packages/profile install
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/profile-install', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $request
    ->setStage('name', 'inceptphp/packages/profile')
    ->setStage('install', __DIR__ . '/install');
  //just do the default installer
  $this('event')->emit('inceptphp/packages-install', $request, $response);
});

/**
 * $ incept inceptphp/packages/profile update
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/profile-update', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $request
    ->setStage('name', 'inceptphp/packages/profile')
    ->setStage('install', __DIR__ . '/install');
  //just do the default installer
  $this('event')->emit('inceptphp/packages-update', $request, $response);
});

/**
 * $ incept inceptphp/packages/profile uninstall
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/profile-uninstall', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $request
    ->setStage('name', 'inceptphp/packages/profile')
    ->setStage('schema', __DIR__ . '/schema');
  //just do the default installer
  $this('event')->emit('inceptphp/packages-uninstall', $request, $response);
});
