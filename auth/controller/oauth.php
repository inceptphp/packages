<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use UGComponents\OAuth\OAuth2;

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

/**
 * Process an OAuth2 Login
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/auth/sso/signin/oauth2/:name', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Declare Packages
  $lang = $this('lang');
  $host = $this('host');
  $http = $this('http');
  $config = $this('config');
  $emitter = $this('event');

  //----------------------------//
  // 2. Prepare Overrides
  //determine route
  $route = $request->getStage('route') ?? '/auth/account';

  //determine redirect
  $redirect = $request->getStage('redirect_uri')
    ?? $config->get('settings', 'home')
    ?? '/';

  //----------------------------//
  // 2. Prepare Data
  $name = $request->getStage('name');
  // get config
  $oauth = $config->get('services', 'oauth2-' . $name);

  if (!$oauth
    || !isset($oauth['client_id'])
    || !isset($oauth['client_secret'])
    || !isset($oauth['url_authorize'])
    || !isset($oauth['url_access_token'])
    || !isset($oauth['url_resource'])
    || !$oauth['active']
  ) {
    //add a flash
    $response->setSession('flash', [
      'message' => $lang->translate('Invalid Service. Try again'),
      'type' => 'error'
    ]);

    return $http->redirect($redirect);
  }

  //get provider
  $provider = new OAuth2(
    // The client ID assigned to you by the provider
    $oauth['client_id'],
    // The client password assigned to you by the provider
    $oauth['client_secret'],
    // http://www.example.com/some/page.html?foo=bar
    $host->url(),
    $oauth['url_authorize'],
    $oauth['url_access_token'],
    $oauth['url_resource']
  );

  //if there is not a code
  if (!$request->hasStage('code')) {
    //we need to know where to go
    $request->setSession('redirect_uri', $redirect);

    if (isset($oauth['scope'])) {
      //set scope
      $scope = $oauth['scope'];
      if (!is_array($oauth['scope'])){
        $scope = [ $oauth['scope'] ];

      }

      $provider->setScope(...$scope);
    }

    //get sign in url
    $loginUrl = $provider->getLoginUrl();
    //redirect
    return $http->redirect($loginUrl);
  }

  //there's a code
  try {
    $accessToken = $provider->getAccessTokens($request->getStage('code'));
  } catch (Throwable $e) {
    // When Graph returns an error
    //add a flash
    $response->setSession('flash', [
      'message' => $e->getMessage(),
      'type' => 'error'
    ]);

    return $http->redirect($redirect);
  }

  if (isset($accessToken['error']) && $accessToken['error']) {
    //add a flash
    $response->setSession('flash', [
      'message' => $lang->translate('Access Token Error'),
      'type' => 'error'
    ]);
    return $http->redirect($redirect);
  }

  if (!isset($accessToken['access_token'])
    || !isset($accessToken['access_secret'])
  ) {
    //add a flash
    $response->setSession('flash', [
      'message' => $lang->translate('Access Token Error'),
      'type' => 'error'
    ]);
    return $http->redirect($redirect);
  }

  $token = $accessToken['access_token'];
  $secret = $accessToken['access_secret'];

  //Now you can get user info
  //access token from $token
  try {
    $user = $provider->get([ 'access_token' => $token ]);
  } catch (Throwable $e) {
    //add a flash
    $response->setSession('flash', [
      'message' => $e->getMessage(),
      'type' => 'error'
    ]);
    return $http->redirect($redirect);
  }

  if (isset($user['error']) && $user['error']) {
    //add a flash
    $response->setSession('flash', [
      'message' => $lang->translate('Resource Request Error'),
      'type' => 'error'
    ]);
    return $http->redirect($redirect);
  }

  //set some defaults
  $request->setStage('profile_email', $user['email']);
  $request->setStage('profile_name', $user['name']);
  $request->setStage('auth_slug', $user['email']);
  $request->setStage('auth_password', $user['id']);
  $request->setStage('auth_active', 1);
  $request->setStage('confirm', $user['id']);
  //there might be more information
  $request->setStage('resource', $user);

  $emitter->emit('auth-sso-login', $request, $response);

  if ($response->isError()) {
    //add a flash
    $response->setSession('flash', [
      'message' => $response->getMessage(),
      'type' => 'error'
    ]);
    return $http->redirect($redirect);
  }

  //it was good
  //store to session
  $response->setSession('me', $response->getResults());
  $response->setSession('me', 'access_token', $token);
  $response->setSession('me', 'access_secret', $secret);

  //if we dont want to redirect
  if ($redirect === 'false') {
    return;
  }

  return $http->redirect($redirect);
});
