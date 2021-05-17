<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

use Incept\Framework\Schema;

/**
 * Add Log
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-object-create', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //if there's an error
  if ($response->isError() || !$request->getSession('me', 'profile_id')) {
    //dont log
    return;
  }

  //get the session profile
  $profile = $request->getSession('me', 'profile_id');
  //get the name
  $name = $request->getStage('schema');
  //stop recursion
  if ($name === 'history') {
    return;
  }

  try {// to load the schema
    $schema = Schema::load($name);
  } catch (Throwable $e) {
    return;
  }

  //get the primary name
  $primary = $schema->getPrimaryName();
  //get the detail
  $detail = $this('event')->call('system-object-detail', [
    'schema' => $name,
    $primary => $response->getResults()
  ]);

  //create the history
  $this('event')->call('system-object-history-create', [
    'history_action' => 'created',
    'history_object' => $name,
    'history_primary' => $response->getResults(),
    'history_to' => $detail,
    'profile_id' => $profile
  ]);
}, -100);

/**
 * Add Log
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-object-remove', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //if there's an error
  if ($response->isError() || !$request->getSession('me', 'profile_id')) {
    //dont log
    return;
  }

  //get the session profile
  $profile = $request->getSession('me', 'profile_id');
  //get the name
  $name = $request->getStage('schema');
  //stop recursion
  if ($name === 'history') {
    return;
  }

  try {// to load the schema
    $schema = Schema::load($name);
  } catch (Throwable $e) {
    return;
  }

  //get the primary name
  $primary = $schema->getPrimaryName();
  //create the history
  $this('event')->call('system-object-history-create', [
    'history_action' => 'removed',
    'history_object' => $name,
    'history_primary' => $request->getStage($primary),
    'profile_id' => $profile
  ]);
}, -100);

/**
 * Add Log
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-object-restore', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //if there's an error
  if ($response->isError() || !$request->getSession('me', 'profile_id')) {
    //dont log
    return;
  }

  //get the session profile
  $profile = $request->getSession('me', 'profile_id');
  //get the name
  $name = $request->getStage('schema');
  //stop recursion
  if ($name === 'history') {
    return;
  }

  try {// to load the schema
    $schema = Schema::load($name);
  } catch (Throwable $e) {
    return;
  }

  //get the primary name
  $primary = $schema->getPrimaryName();
  //create the history
  $this('event')->call('system-object-history-create', [
    'history_action' => 'restored',
    'history_object' => $name,
    'history_primary' => $request->getStage($primary),
    'profile_id' => $profile
  ]);
}, -100);

/**
 * Add Log
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-object-update', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //if there's an error
  if ($response->isError() || !$request->getSession('me', 'profile_id')) {
    //dont log
    return;
  }

  //get the session profile
  $profile = $request->getSession('me', 'profile_id');
  //get the name
  $name = $request->getStage('schema');
  //stop recursion
  if ($name === 'history') {
    return;
  }

  try {// to load the schema
    $schema = Schema::load($name);
  } catch (Throwable $e) {
    return;
  }

  //get the primary name
  $primary = $schema->getPrimaryName();
  //get the detail
  $detail = $this('event')->call('system-object-detail', [
    'schema' => $name,
    $primary => $request->getStage($primary)
  ]);

  //create the history
  $this('event')->call('system-object-history-create', [
    'history_action' => 'updated',
    'history_object' => $name,
    'history_primary' => $request->getStage($primary),
    'history_from' => $response->getResults('original'),
    'history_to' => $detail,
    'profile_id' => $profile
  ]);
}, -100);
