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
