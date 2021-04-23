<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Storm\SqlFactory;

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

/**
 * Storm Flush
 *
 * $ incept sql flush
 * $ incept sql-flush
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('sql-flush', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //if there's already an error
  if ($response->isError()) {
    //dont continue
    return;
  }

  //load some packages
  $terminal = $this('terminal');

  $terminal->system('Flushing SQL...');

  $config = $this('config')->get('sql-main');
  $database = SqlFactory::load($config);

  //truncate all tables
  $tables = $database->getTables();

  if (empty($tables)) {
    $terminal->error('No tables found', false);
    return;
  }

  //whether to ask questions
  $force = $request->hasStage('f') || $request->hasStage('force');
  $message = 'This will truncate tables in your existing database. Are you sure?(y)';
  //if not force and answer is not yes
  if (!$force && $terminal->input($message, 'y') !== 'y') {
    $terminal->warning('Aborting...');
    return;
  }

  // iterate on each tables
  foreach ($tables as $table) {
    $terminal->info(sprintf('Flushing %s', $table));
    $database->query('TRUNCATE TABLE `' . $table . '`;');
  }
});
