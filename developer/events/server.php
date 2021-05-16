<?php //-->
/**
 * This file is part of the incept PHP Library.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

/**
 * $ incept server -h 127.0.0.1 -p 8888
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('server', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $port = 8888;

  if($request->hasStage('port')) {
    $port = $request->getStage('port');
  } else if($request->hasStage('p')) {
    $port = $request->getStage('p');
  }

  $host = '127.0.0.1';

  if($request->hasStage('host')) {
    $host = $request->getStage('host');
  } else if($request->hasStage('h')) {
    $host = $request->getStage('h');
  }

  //setup the configs
  $this('terminal')
    ->system('Starting Server...')
    ->info('Listening on ' . $host . ':'.$port)
    ->info('Press Ctrl-C to quit.');

  $cwd = getcwd();
  $router = dirname(__DIR__) . '/router.php';
  system(sprintf('php -S %s:%s -t %s/public %s', $host, $port, $cwd, $router));
});
