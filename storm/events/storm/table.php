<?php //-->

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

/**
 * Database insert Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('storm-insert', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 0. Abort on Errors
  if ($response->isError()) {
    return;
  }

  //----------------------------//
  // 1. Set the Resources
  if ($request->stormInsert) {
    $resource = $request->stormInsert;
  } else {
    $resource = $this('storm')->insert();
  }

  //----------------------------//
  // 2. Get Data
  $table = $request->getStage('table');
  //eg. [[product_title => ['value' => 'Some Title', 'bind' => true]]]
  $rows = $request->getStage('rows');

  if (!is_array($rows) && is_array($request->getStage('data'))) {
    $rows = [$request->getStage('data')];
  } else if (!is_array($rows)) {
    $rows = [];
  }

  //----------------------------//
  // 3. Validate Data
  //we need at least a table
  if (!trim($table)) {
    $response->invalidate('table', 'Table is required');
  }

  if (empty($rows)) {
    $response->invalidate('rows', 'Empty rows');
  } else {
    //all rows should be an array (hash)
    foreach ($rows as $row) {
      if (!is_array($row)) {
        $response->invalidate('rows', 'One or more rows are invalid');
        break;
      }
    }
  }

  if (!$response->isValid()) {
    return $response->setError(true, 'Invalid Parameters');
  }

  //----------------------------//
  // 4. Prepare Data
  $resource->setTable($table);

  //----------------------------//
  // 5. Process Data
  //whether or not to insert with primary keys
  $withPrimary = !!$request->getStage('with_primary');
  foreach ($rows as $index => $row) {
    //remove columns that are not in this table
    $row = $this('storm')->getValidData($table, $row, $withPrimary);
    //loop through each key
    foreach ($row as $key => $value) {
      if (is_scalar($value)) {
        $resource->set($key, $value, true, $index);
      } else if (is_array($value)) {
        $resource->set($key, $value['value'], $value['bind'], $index);
      }
    }
  }

  $resource->query();
  $id = $resource->getDatabase()->getLastInsertedId();
  $response->setError(false)->setResults($id);
});

/**
 * Database delete Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('storm-delete', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 0. Abort on Errors
  if ($response->isError()) {
    return;
  }

  //----------------------------//
  // 1. Set the Resources
  if ($request->stormRemove) {
    $resource = $request->stormRemove;
  } else {
    $resource = $this('storm')->remove();
  }

  //----------------------------//
  // 2. Get Data
  $table = $request->getStage('table');
  //eg. joins = [['type' => 'inner', 'table' => 'product', 'where' => 'product_id']]
  $joins = $request->getStage('joins');
  //eg. filters = [['where' => 'product_id =%s', 'binds' => [1]]]
  $filters = $request->getStage('filters');

  if (!is_array($joins)) {
    $joins = [];
  }

  if (!is_array($filters)) {
    $filters = [];
  }

  //----------------------------//
  // 3. Validate Data
  $errors = [];
  //we need at least a table
  if (!trim($table)) {
    $errors['table'] = 'Table is required';
  }

  if (!empty($errors)) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->setValidation($errors);
  }

  //----------------------------//
  // 3. Prepare Data
  $resource->setTable($table);

  $validJoinTypes = ['inner', 'left', 'right', 'outer'];
  foreach($joins as $join) {
    if (!isset($join['type'], $join['table'], $join['where'])
      || !in_array($join['type'], $validJoinTypes)
    ) {
      continue;
    }

    $link = 'On';
    if (preg_match('^[a-zA-Z0-9_]+$', $join['where'])) {
      $link = 'Using';
    }

    $method = sprintf('%sJoin%s', $join['type'], $link);
    $resource->$method($join['table'], $join['where']);
  }

  foreach($filters as $filter) {
    if (!isset($filter['where'])) {
      continue;
    }

    if (!isset($filter['binds']) || !is_array($filter['binds'])) {
      $filter['binds'] = [];
    }

    $binds = $filter['binds'];

    $resource->addFilter($filter['where'], ...$binds);
  }

  //----------------------------//
  // 5. Process Data
  $results = $resource->query();
  $response->setError(false)->setResults($results);
});

/**
 * Database search Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('storm-search', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 0. Abort on Errors
  if ($response->isError()) {
    return;
  }

  //----------------------------//
  // 1. Set the Resources
  if ($request->stormSearch) {
    $resource = $request->stormSearch;
  } else {
    $resource = $this('storm')->search();
  }

  //----------------------------//
  // 2. Get Data
  $table = $request->getStage('table');
  $columns = $request->getStage('columns');
  //eg. joins = [['type' => 'inner', 'table' => 'product', 'where' => 'product_id']]
  $joins = $request->getStage('joins');
  //eg. filters = [['where' => 'product_id =%s', 'binds' => [1]]]
  $filters = $request->getStage('filters');
  //eg. group = ['product_id']
  $group = $request->getStage('group');
  //eg. having = [['where' => 'product_id =%s', 'binds' => [1]]]
  $having = $request->getStage('having');
  //eg. sort = ['product_id' => 'ASC']
  $sort = $request->getStage('sort');

  if (!$columns) {
    $columns = '*';
  }

  if (!is_array($joins)) {
    $joins = [];
  }

  if (!is_array($filters)) {
    $filters = [];
  }

  if (!is_array($group)) {
    $group = [];
  }

  if (!is_array($having)) {
    $having = [];
  }

  if (!is_array($sort)) {
    $sort = [];
  }

  //----------------------------//
  // 3. Validate Data
  $errors = [];
  //we need at least a table
  if (!trim($table)) {
    $errors['table'] = 'Table is required';
  }

  if (!empty($errors)) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->setValidation($errors);
  }

  //----------------------------//
  // 4. Prepare Data
  $resource->setColumns($columns)->setTable($table)->from($table);

  $validJoinTypes = ['inner', 'left', 'right', 'outer'];
  foreach($joins as $join) {
    if (!isset($join['type'], $join['table'], $join['where'])
      || !in_array($join['type'], $validJoinTypes)
    ) {
      continue;
    }

    $link = 'On';
    if (preg_match('/^[a-zA-Z0-9_]+$/', $join['where'])) {
      $link = 'Using';
    }

    $method = sprintf('%sJoin%s', $join['type'], $link);
    $resource->$method($join['table'], $join['where']);
  }

  foreach($filters as $filter) {
    if (!isset($filter['where'])) {
      continue;
    }

    if (!isset($filter['binds']) || !is_array($filter['binds'])) {
      $filter['binds'] = [];
    }

    $binds = $filter['binds'];

    $resource->addFilter($filter['where'], ...$binds);
  }

  if (!empty($group)) {
    $resource->groupBy($group);
  }

  if (!empty($having)) {
    $resource->having($having);
  }

  if (!empty($sort)) {
    foreach ($sort as $column => $direction) {
      $resource->addSort($column, $direction);
    }
  }

  if (is_numeric($request->getStage('start'))) {
    $resource->setStart($request->getStage('start'));
  }

  if (is_numeric($request->getStage('range'))) {
    $resource->setRange($request->getStage('range'));
  }

  //----------------------------//
  // 5. Process Data
  $results = $resource->getRows();
  if ($request->hasStage('with_total')) {
    $results = [
      'rows' => $results,
      'total' => $resource->getTotal()
    ];
  }

  $response->setError(false)->setResults($results);
});

/**
 * Database update Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('storm-update', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 0. Abort on Errors
  if ($response->isError()) {
    return;
  }

  //----------------------------//
  // 1. Set the Resources
  if ($request->stormUpdate) {
    $resource = $request->stormUpdate;
  } else {
    $resource = $this('storm')->update();
  }

  //----------------------------//
  // 2. Get Data
  $table = $request->getStage('table');
  //eg. data = [product_title => ['value' => 'Some Title', 'bind' => true]]
  $data = $request->getStage('data');
  //eg. joins = [['type' => 'inner', 'table' => 'product', 'where' => 'product_id']]
  $joins = $request->getStage('joins');
  //eg. filters = [['where' => 'product_id =%s', 'binds' => [1]]]
  $filters = $request->getStage('filters');

  if (!is_array($joins)) {
    $joins = [];
  }

  if (!is_array($filters)) {
    $filters = [];
  }

  if (!is_array($data)) {
    $data = [];
  }

  //----------------------------//
  // 3. Validate Data
  $errors = [];
  //we need at least a table
  if (!trim($table)) {
    $response->invalidate('table', 'Table is required');
  }

  if(!is_array($data) || empty($data)) {
    $response->invalidate('data', 'Data is required');
  }

  foreach ($data as $name => $value) {
    if (!is_scalar($value) && !isset($value['value'], $value['bind'])) {
      $response->invalidate($name, 'Data format is invalid');
    }
  }

  if (!$response->isValid()) {
    return $response->setError(true, 'Invalid Parameters');
  }

  //----------------------------//
  // 4. Prepare Data
  $resource->setTable($table);

  $validJoinTypes = ['inner', 'left', 'right', 'outer'];
  foreach($joins as $join) {
    if (!isset($join['type'], $join['table'], $join['where'])
      || !in_array($join['type'], $validJoinTypes)
    ) {
      continue;
    }

    $link = 'On';
    if (preg_match('^[a-zA-Z0-9_]+$', $join['where'])) {
      $link = 'Using';
    }

    $method = sprintf('%sJoin%s', $join['type'], $link);
    $resource->$method($join['table'], $join['where']);
  }

  foreach($filters as $filter) {
    if (!isset($filter['where'])) {
      continue;
    }

    if (!isset($filter['binds']) || !is_array($filter['binds'])) {
      $filter['binds'] = [];
    }

    $binds = $filter['binds'];

    $resource->addFilter($filter['where'], ...$binds);
  }

  //remove columns that are not in this table
  $data = $this('storm')->getValidData($table, $data);
  //loop through each key
  foreach ($data as $key => $value) {
    if (is_scalar($value)) {
      $resource->set($key, $value, true);
    } else if (is_array($value)) {
      $resource->set($key, $value['value'], $value['bind']);
    }
  }

  //----------------------------//
  // 5. Process Data
  $results = $resource->query();
  $response->setError(false)->setResults($results);
});
