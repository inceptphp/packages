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
 * Session Access Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('rest-access', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Get Data
  $data = [];
  if ($request->hasStage()) {
    $data = $request->getStage();
  }

  //----------------------------//
  // 2. Validate Data
  //code Required
  if (!isset($data['code']) || empty($data['code'])) {
    $errors['code'] = 'Cannot be empty';
  }

  //client_id Required
  if (!isset($data['client_id']) || empty($data['client_id'])) {
    $errors['client_id'] = 'Cannot be empty';
  }

  //client_secret Required
  if (!isset($data['client_secret']) || empty($data['client_secret'])) {
    $errors['client_secret'] = 'Cannot be empty';
  }

  //if there are errors
  if (!empty($errors)) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->set('json', 'validation', $errors);
  }

  //get the session detail
  $request->setStage('session_token', $data['code']);
  $this('event')->emit('system-object-session-detail', $request, $response);

  if ($data['client_id'] !== $response->getResults('app_token')) {
    $response
      ->setError(true, 'Invalid Parameters')
      ->invalidate('client_id', 'Token does not belong with this session');
  }

  if ($data['client_secret'] !== $response->getResults('app_secret')) {
    $response
      ->setError(true, 'Invalid Parameters')
      ->invalidate('client_secret', 'Token does not belong with this session');
  }

  //if there's an error
  if ($response->isError()) {
    return;
  }

  //----------------------------//
  // 3. Process Data
  $current = $response->getResults();

  $request
    ->setStage('session_id', $current['session_id'])
    ->setStage('session_token', md5(uniqid()))
    ->setStage('session_secret', md5(uniqid()))
    ->setStage('session_status', 'access');

  $this('event')->emit('system-object-session-update', $request, $response);

  //if there's an error
  if ($response->isError()) {
    return;
  }

  $results = [];
  $results['access_token'] = $response->getResults('session_token');
  $results['access_secret'] = $response->getResults('session_secret');

  //return response format
  $response->set('json', $results);
});

/**
 * Resource request profile_id
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('rest-resource', function(
  RequestInterface $request,
  ResponseInterface $response
) {
  $resources = $this('event')->call('system-collection-auth-search', [
    'filter' => [
      'profile_id' => $request->getStage('profile_id')
    ]
  ]);

  if (!isset($resources['rows'][0])) {
    return $response->setError(true, 'No resource found');
  }

  $resource = [
    'id' => $resources['rows'][0]['auth_id'],
    'email' => $resources['rows'][0]['auth_slug'],
    'name' => $resources['rows'][0]['profile_name'],
    'created' => $resources['rows'][0]['auth_created']
  ];

  $response->set('json', $resource);
});

/**
 * OAuth App Permission Check
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('rest-source-app-detail', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  if (!$request->hasStage('client_id')) {
    return $response->setError(true, 'Unauthorize Request');
  }

  $token = $request->getStage('client_id');
  $secret = $request->getStage('client_secret');

  if ($request->getMethod() !== 'GET' && !$secret) {
    return $response->setError(true, 'Unauthorize Request');
  }

  $filters = [];
  $filters['app_token'] = $token;
  $filters['app_active'] = 1;

  if ($secret) {
    $filters['app_secret'] = $secret;
  }

  $results = $this('event')->call('system-object-app-search', [
    'filter' => $filters
  ]);

  if (!$results['total']) {
    return $response->setError(true, 'Unauthorize Request');
  }

  $row = $this('event')->call('system-object-app-detail', [
    'app_id' => $results['rows'][0]['app_id']
  ]);

  $response->setResults($row);
  $response->setResults('type', 'app');
  $response->setResults('token', $token);
  $response->setResults('secret', $secret);

  return $response->setError(false);
});

/**
 * OAuth Session Permission Check
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('rest-source-session-detail', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  if (!$request->hasStage('access_token')) {
    return $response->setError(true, 'Unauthorize Request');
  }

  $token = $request->getStage('access_token');
  $secret = $request->getStage('access_secret');

  if ($request->getMethod() !== 'GET' && !$secret) {
    return $response->setError(true, 'Unauthorize Request');
  }

  $filters = [];
  $filters['session_token'] = $token;
  $filters['session_status'] = 'access';
  $filters['session_active'] = 1;

  if ($secret) {
    $filters['session_secret'] = $secret;
  }

  $results = $this('event')->call('system-collection-session-search', [
    'filter' => $filters
  ]);

  if (!$results['total']) {
    return $response->setError(true, 'Unauthorize Request');
  }

  $row = $this('event')->call('system-object-session-detail', [
    'session_id' => $results['rows'][0]['session_id']
  ]);

  $response->setResults($row);
  $response->setResults('type', 'session');
  $response->setResults('token', $token);
  $response->setResults('secret', $secret);

  return $response->setError(false);
});

/**
 * Gets all the rest calls given the source scopes
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('rest-route-search', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $results = [];
  //first get all the public calls
  $rows = $this('event')->call('system-collection-rest-search', [
    'filter' => [
      'rest_type' => 'public'
    ]
  ])['rows'];
  //add it to results
  foreach ($rows as $row) {
    if (!is_array($row['rest_parameters'])) {
      $row['rest_parameters'] = [];
    }

    $results[$row['rest_id']] = $row;
  }

  //next get all the
  $scopes = $request->get('source', 'scope');

  if (!is_array($scopes)) {
    $scopes = [];
  }

  //just need the scope ids
  $ids = [];
  foreach ($scopes as $scope) {
    $ids[] = $scope['scope_id'];
  }

  if (!empty($ids)) {
    $rows = $this('event')->call('system-collection-rest-search', [
      'join' => ['rest'],
      'filter' => [
        'rest_type' => 'public'
      ],
      'in' => [
        'scope_id' => $ids
      ]
    ]);

    //add it to results
    foreach ($rows as $row) {
      $row['rest_parameters'] = json_decode(
        $row['rest_parameters'],
        true
      );

      if (!is_array($row['rest_parameters'])) {
        $row['rest_parameters'] = [];
      }

      $results[$row['rest_id']] = $row;
    }
  }

  $response->setResults([
    'rows' => array_values($results),
    'total' => count($results)
  ]);
});

/**
 * Gets all the rest calls given the source scopes
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('rest-batch', function(
  RequestInterface $request,
  ResponseInterface $response
) {
  if (!is_array($request->getStage('batch'))
    || $response->isError()
    || $response->hasResults()
  ) {
    return;
  }

  $batch = $request->getStage('batch');

  $responses = [];
  foreach ($batch as $i => $call) {
    //validate
    if (!isset($call['method'])) {
      $responses[$i] = [
        'error' => true,
        'message' => 'Batch call missing method'
      ];

      continue;
    } else if (!isset($call['path'])) {
      $responses[$i] = [
        'error' => true,
        'message' => 'Batch call missing path'
      ];

      continue;
    } else if (!preg_match('#^/org/[0-9]+/rest/#', $call['path'])) {
      //should start with /org/:org_id/rest/
      $responses[$i] = [
        'error' => true,
        'message' => 'Batch call path is an invalid REST path'
      ];

      continue;
    }

    //make sure data is an array
    if (!isset($call['data']) || !is_array($call['data'])) {
      $call['data'] = [];
    }

    //make the payload
    $payload = $this->makePayload();
    //reset the data with the given data
    $payload['request']->set('stage', [])->setStage($call['data']);
    //route to the REST call
    $this('http')->routeTo(
      $call['method'],
      $call['path'],
      $payload['request'],
      $payload['response']
    );
    //store the results
    $responses[$i] = $payload['response']->get('json');
  }

  $response->setError(false)->setResults($responses);
});
