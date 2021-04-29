<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

/**
 * Process the sign out
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/auth/signout', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Declare Packages
  $http = $this('http');
  $lang = $this('lang');
  $config = $this('config');

  //----------------------------//
  // 2. Process Request
  $response->removeSession('me');

  //add a flash
  $response->setSession('flash', [
    'message' => $lang->translate('Sign Out Successful'),
    'type' => 'success'
  ]);

  //redirect
  $redirect = $request->getStage('redirect_uri')
    ?? $config->get('settings', 'home')
    ?? '/';

  $http->redirect($redirect);
});
