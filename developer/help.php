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
 * $ incept help
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('help', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $this('terminal')
    ->output(PHP_EOL)

    ->warning('Project Maintenance Commands:')

    ->output(PHP_EOL)

    ->success('incept install')
    ->info(' Installs Project')
    ->info(' - Example: incept install')
    ->info(' - Example: incept install --force')
    ->info(' - Example: incept install testing_db -h 127.0.0.1 -u root -p root --force')
    ->info(' - Flags:')
    ->info('   --force -f - Skips asking questions')
    ->info('   --skip-configs - Skips config file setup')
    ->info('   --skip-mkdir - Skips folder creation')
    ->info('   --skip-chmod - Skips chmodding')
    ->info('   --skip-sql - Skips SQL installation')
    ->info('   --skip-versioning - Skips version updates')

    ->output(PHP_EOL)

    ->success('incept update')
    ->info(' Updates Project with versioning install scripts')
    ->info(' - Example: incept update')

    ->output(PHP_EOL)

    ->success('incept server')
    ->info(' Starts up the PHP server (dev mode)')
    ->info(' - Example: incept server')
    ->info(' - Example: incept server -h 127.0.0.1 -p 8888')

    ->output(PHP_EOL);
});
