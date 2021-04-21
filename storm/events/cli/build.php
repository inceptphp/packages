<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Storm\SqlFactory;
use Incept\Framework\Schema;

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

/**
 * Storm Build
 *
 * $ incept sql build
 * $ incept sql-build
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('sql-build', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //if there's already an error
  if ($response->isError()) {
    //dont continue
    return;
  }

  //load some packages
  $emitter = $this('event');
  $terminal = $this('terminal');

  $emitter->emit('sql-build-database', $request, $response);

  if ($response->isError()) {
    $terminal->error($response->getMessage());
    return;
  }

  $emitter->emit('sql-build-schema', $request, $response);

  if ($response->isError()) {
    $terminal->error($response->getMessage());
    return;
  }
});

/**
 * Storm Installer - Database Setup
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('sql-build-database', function (
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

  //whether to ask questions
  $force = $request->hasStage('f') || $request->hasStage('force');

  //config
  $key = $this('config')->get('settings', 'pdo');
  $config = $this('config')->get('services');
  if (!isset($config[$key]['name'])) {
    return $response->setError(true, 'Database name not found.');
  }

  //connections
  $build = $this('storm')->get('sql-build');
  $main = $this('storm')->get($key);

  $exists = $build->query(sprintf(
    "SHOW DATABASES LIKE '%s';",
    $config[$key]['name']
  ));

  //if force then continue
  //if empty then continue
  $continue = empty($exists) || $force;

  //if continue, then continue (it's too long to put in one line)
  if (!$continue) {
    $question = 'This will override your existing database. Are you sure?(y)';
    $continue = $terminal->input($question, 'y') === 'y';
  }

  if (!$continue) {
    return $response->setError(false);
  }

  $terminal->system('Building Database...');

  //use build to create the database
  $build->query(sprintf(
    'CREATE DATABASE IF NOT EXISTS `%s`;',
    $config[$key]['name']
  ));

  //drop all tables
  $tables = $main->getTables();
  foreach ($tables as $table) {
    $main->query(sprintf('DROP TABLE `%s`;', $table));
  }

  $response->setError(false);
});

/**
 * Storm Installer - Install Schema to Database
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('sql-build-schema', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //if there's already an error
  if ($response->isError() || !class_exists(Schema::class)) {
    //dont continue
    return;
  }

  $rows = Schema::search();

  foreach ($rows as $schema) {
    //make a new empty payload
    $payload = $this->makePayload();
    //add the results
    $payload['request']->setStage($schema->get());
    //set the primary name
    $payload['request']->setStage('primary', $schema->getPrimaryName());
    //re-add the fields with all possible types
    $payload['request']->setStage('fields', $schema->getFields());
    //re-add the relations
    $payload['request']->setStage('relations', $schema->getRelations());

    //trigger the store create
    $this('event')->emit(
      'system-store-create',
      $payload['request'],
      $payload['response']
    );

    if ($payload['response']->isError()) {
      $response->invalidate(
        $schema->getName(),
        $payload['response']->getMessage()
      );
    }
  }

  if (!$response->isValid()) {
    return $response->setError(true, 'Some schemas were not installed');
  }

  $response->setError(false);
});
