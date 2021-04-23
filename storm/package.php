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
 * Storm Event Forwarder
 *
 * $ incept sql
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('sql', function (
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

  $this('event')->emit('sql-' . $event, $request, $response);
});

/**
 * SQL help menu
 *
 * $ incept sql help
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('sql-help', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $this('terminal')
    ->warning('SQL Commands:')

    ->output(PHP_EOL)

    ->success('incept sql flush')
    ->info(' Trancates SQL database')
    ->info(' Example: bin/incept sql flush')

    ->output(PHP_EOL)

    ->success('incept sql build')
    ->info(' Builds SQL schema on database')
    ->info(' Example: bin/incept sql build')

    ->output(PHP_EOL);
});

/**
 * Storm Installer
 *
 * $ incept install
 * $ incept install -f | --force
 * $ incept install --skip-sql
 * $ incept install -h 127.0.0.1 -u root -p 123
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/storm-install', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  if ($request->hasStage('skip-sql')) {
    return;
  }

  $this('event')->emit('sql-install', $request, $response);

  //if there's an error
  if ($response->isError()) {
    //dont continue
    return;
  }

  //load some packages
  $config = $this('config');
  $terminal = $this('terminal');

  //custom name of this package
  $name = 'inceptphp/packages/storm';
  $version = '0.0.1';

  // update the config
  $config->set('packages', $name, [
    'version' => $version,
    'active' => true,
    'locked' => false
  ]);

  $terminal->success(sprintf('%s %s installed', $name, $version));
  $response->setError(false);
});
