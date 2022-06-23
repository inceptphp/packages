<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Incept\Framework\Schema;

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

/**
 * Creates a auth
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('auth-create', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Declare Packages
  $lang = $this('lang');
  $emitter = $this('event');

  //----------------------------//
  // 1. Get Data
  $data = $request->getStage() ?? [];

  if (!isset($data['auth_active'])) {
    $request->setStage('auth_active', 0);
  }

  //----------------------------//
  // 2. Validate Data
  $auth = Schema::load('auth');
  $profile = Schema::load('profile');

  $errors = array_merge(
    $auth->getErrors($data),
    $profile->getErrors($data)
  );

  //auth will require profile
  unset($errors['profile_id']);

  //if there are errors
  if (!empty($errors)) {
    return $response
      ->setError(true, 'Invalid Parameters')
      ->invalidate($errors);
  }

  //----------------------------//
  // 3. Process Data
  // check profile
  if (!isset($data['profile_id'])) {
    // trigger model create
    $emitter->emit('system-object-profile-create', $request, $response);

    if ($response->isError()) {
      return;
    }

    // set profile id
    $request->setStage('profile_id', $response->getResults());
  }

  //trigger model create
  $emitter->emit('system-object-auth-create', $request, $response);

  //remove password, confirm
  $response->removeResults('auth_password');
});

/**
 * Get the auth detail for verification
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('auth-detail', function (
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
  if (!isset($data['auth_slug']) || !trim((string) $data['auth_slug'])) {
    $response->invalidate('auth_slug', 'Cannot be empty');
  }

  //if there are errors
  if (!$response->isValid()) {
    return $response->setError(true, 'Invalid Parameters');
  }

  //----------------------------//
  // 3. Prepare Data
  $schema = Schema::load('auth');

  //make a new payload
  $payload = $request->clone(true);

  //eg. joins = [['type' => 'inner', 'table' => 'product', 'where' => 'product_id']]
  $joins = $this('system')->getInnerJoins($schema, $data);
  //eg. filters = [['where' => 'product_id =%s', 'binds' => [1]]]
  $match = [
    'auth_username = %s',
    '(auth_email = %s AND auth_email_verified = 1)',
    '(auth_phone = %s AND auth_phone_verified = 1)'
  ];

  $filters = [
    [
      'where' => sprintf('(%s)', implode(' OR ', $match)),
      'binds' => [$data['auth_slug'], $data['auth_slug'], $data['auth_slug']]
    ],
    [ 'where' => 'auth_active = 1', 'binds' => [] ]
  ];

  if (isset($data['check']) && $data['check']) {
    $match = ['auth_username = %s', 'auth_email = %s', 'auth_phone = %s'];
    $filters = [
      [
        'where' => sprintf('(%s)', implode(' OR ', $match)),
        'binds' => [$data['auth_slug'], $data['auth_slug'], $data['auth_slug']]
      ]
    ];
  }

  //set the payload
  $payload->setStage([
    'table' => 'auth',
    'columns' => '*',
    'joins' => $joins,
    'filters' => $filters,
    'start' => 0,
    'range' => 1,
    'test' => 'me'
  ]);

  //----------------------------//
  // 4. Process Data
  $results = $this('event')->call('system-store-search', $payload, $response);
  if ($response->isError()) {
    return;
  }

  if (!isset($results[0])) {
    return $response->setError(true, 'Not found');
  }

  $response
    ->setError(false)
    ->setResults($results[0]);
});

/**
 * Auth SSO Login Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('auth-sso-login', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //get data
  $data = [];
  if ($request->hasStage()) {
    $data = $request->getStage();
  }

  //load up the detail
  $this->trigger('auth-detail', $request, $response);
  if ($request->getStage('profile') && !$response->isError()) {
    return $response->setError(true);
  }

  //if there's an error
  if ($response->isError()) {
    //they don't exist
    $auth = $this->method('auth-create', $request);
    $response->setResults($auth);
  }

  $response->setError(false)->remove('json', 'message');

  // if auth is not active yet, update
  if (!$response->getResults('auth_active')) {
    $request->setStage('auth_active', 1);
    $request->setStage('auth_id', $response->getResults('auth_id'));
    $this->trigger('auth-update', $request, $response);
  }

  //load up the detail
  $this->trigger('auth-detail', $request, $response);
});
