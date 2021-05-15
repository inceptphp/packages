<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

use Incept\Framework\Schema;

/**
 * Custom post category search
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('post-category-search', function(
  RequestInterface $request,
  ResponseInterface $response
) {
  $filter = [];
  $range = 50;
  $start = 0;
  $order = [];

  $data = $request->getStage();

  if (isset($data['filter']) && is_array($data['filter'])) {
    $filter = $data['filter'];
  }

  if (isset($data['range']) && is_numeric($data['range'])) {
    $range = $data['range'];
  }

  if (isset($data['start']) && is_numeric($data['start'])) {
    $start = $data['start'];
  }

  if (isset($data['order']) && is_array($data['order'])) {
    $order = $data['order'];
  }

  $search = $this('storm')
    ->search('category')
    ->setColumns('DISTINCT(category_id)', 'category.*')
    ->innerJoinUsing('post_category', 'category_id')
    ->setStart($start);

  if ($range) {
    $search->setRange($range);
  }

  //add filters
  foreach ($filter as $column => $value) {
    if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
      $search->addFilter($column . ' = %s', $value);
      continue;
    }
  }

  //keyword?
  if (isset($data['q'])) {
    $search->addFilter('comment_detail LIKE %s', '%' . $data['q'] . '%');
  }

  //add sorting
  foreach ($order as $sort => $direction) {
    if (preg_match('/^[a-zA-Z0-9-_]+$/', $sort)) {
      $search->addSort($sort, $direction);
      continue;
    }
  }

  $rows = $search->getRows();

  //unpack the json
  foreach ($rows as $i => $row) {
    foreach ($row as $key => $value) {
      if ((strpos($value, '[') && strrpos($value, ']'))
        || (strpos($value, '{') && strrpos($value, '}'))
      ) {
        $rows[$i][$key] = json_decode($value, true);
      }
    }
  }

  //return response format
  $response->setResults([
    'rows' => $rows,
    'total' => $search->getTotal()
  ]);
});

/**
 * Custom category detail
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('post-category-detail', function(
  RequestInterface $request,
  ResponseInterface $response
) {
  $this('event')->emit('system-object-category-detail', $request, $response);

  if ($response->isError()) {
    return;
  }

  $search = $this('storm')
    ->search('post')
    ->innerJoinUsing('post_profile', 'post_id')
    ->innerJoinUsing('profile', 'profile_id')
    ->innerJoinUsing('post_category', 'post_id')
    ->innerJoinUsing('category', 'category_id');

  if ($request->hasStage('category_id')) {
    $search->filterByCategoryId($request->getStage('category_id'));
  } else if ($request->hasStage('category_slug')) {
    $search->filterByCategorySlug($request->getStage('category_slug'));
  } else {
    return $response->setError(true, 'No Category ID provided');
  }

  $rows = $search->getRows();

  //unpack the json
  foreach ($rows as $i => $row) {
    foreach ($row as $key => $value) {
      if ((strpos($value, '[') && strrpos($value, ']'))
        || (strpos($value, '{') && strrpos($value, '}'))
      ) {
        $rows[$i][$key] = json_decode($value, true);
      }
    }
  }

  //return response format
  $response
    ->setResults('rows', $rows)
    ->setResults('total', $search->getTotal());
});

/**
 * Custom comment search
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('post-comment-search', function(
  RequestInterface $request,
  ResponseInterface $response
) {
  $filter = [];
  $range = 50;
  $start = 0;
  $order = [];

  $data = $request->getStage();

  if (isset($data['filter']) && is_array($data['filter'])) {
    $filter = $data['filter'];
  }

  if (isset($data['range']) && is_numeric($data['range'])) {
    $range = $data['range'];
  }

  if (isset($data['start']) && is_numeric($data['start'])) {
    $start = $data['start'];
  }

  if (isset($data['order']) && is_array($data['order'])) {
    $order = $data['order'];
  }

  $search = $this('storm')
    ->search('comment')
    ->innerJoinUsing('comment_profile', 'comment_id')
    ->innerJoinUsing('profile', 'profile_id')
    ->innerJoinUsing('post_comment', 'comment_id')
    ->innerJoinUsing('post', 'post_id')
    ->setStart($start);

  if ($range) {
    $search->setRange($range);
  }

  //add filters
  foreach ($filter as $column => $value) {
    if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
      $search->addFilter($column . ' = %s', $value);
      continue;
    }
  }

  //keyword?
  if (isset($data['q'])) {
    $search->addFilter('comment_detail LIKE %s', '%' . $data['q'] . '%');
  }

  //add sorting
  foreach ($order as $sort => $direction) {
    if (preg_match('/^[a-zA-Z0-9-_]+$/', $sort)) {
      $search->addSort($sort, $direction);
      continue;
    }
  }

  $rows = $search->getRows();

  //unpack the json
  foreach ($rows as $i => $row) {
    foreach ($row as $key => $value) {
      if ((strpos($value, '[') && strrpos($value, ']'))
        || (strpos($value, '{') && strrpos($value, '}'))
      ) {
        $rows[$i][$key] = json_decode($value, true);
      }
    }
  }

  //return response format
  $response->setResults([
    'rows' => $rows,
    'total' => $search->getTotal()
  ]);
});

/**
 * Custom post archive search
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('post-archive-search', function(
  RequestInterface $request,
  ResponseInterface $response
) {
  $rows = $this('storm')
    ->search('post')
    ->setColumns(
      'YEAR(post_published) as year',
      'MONTH(post_published) as month'
    )
    ->innerJoinUsing('post_profile', 'post_id')
    ->filterByPostActive(1)
    ->filterByPostPublic(1)
    ->addFilter('post_published < NOW()')
    ->groupBy('YEAR(post_published), MONTH(post_published) DESC')
    ->getRows();

  foreach($rows as $i => $row) {
    $rows[$i]['date'] = $row['year'] . '-' . $row['month'] . '-01';
  }

  //return response form
  $response->setResults([
    'rows' => $rows
  ]);
});

/**
 * Custom post archive details
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('post-archive-detail', function(
  RequestInterface $request,
  ResponseInterface $response
) {
  $errors = [];
  if (!$request->hasStage('year')) {
    $errors['year'] = 'Year is required';
  }

  if (!$request->hasStage('month')) {
    $errors['month'] = 'Month is required';
  }

  if (!empty($errors)) {
    $response->setError(true, 'Invalid Parameters');
    return $response->set('json', 'validation', $errors);
  }

  $year = $request->getStage('year');
  $month = $request->getStage('month');
  $search = $this('storm')
    ->search('post')
    ->innerJoinUsing('post_profile', 'post_id')
    ->innerJoinUsing('profile', 'profile_id')
    ->addFilter('YEAR(post_published) = %s', $year)
    ->addFilter('MONTH(post_published) = %s', $month);

  $rows = $search->getRows();

  //unpack the json
  foreach ($rows as $i => $row) {
    foreach ($row as $key => $value) {
      if ((strpos($value, '[') && strrpos($value, ']'))
        || (strpos($value, '{') && strrpos($value, '}'))
      ) {
        $rows[$i][$key] = json_decode($value, true);
      }
    }
  }

  //return response format
  $response->setResults([
    'archive' => $year . '-' . $month . '-01',
    'rows' => $rows,
    'total' => $search->getTotal()
  ]);
});
