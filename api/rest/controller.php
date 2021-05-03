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
 * Process REST Access
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/rest/access', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //set the profile id
  $profile = $request->get('source', 'profile_id');
  $request->setStage('permission', $profile);

  //call the job
  $this('event')->emit('rest-access', $request, $response);
});

/**
 * Process REST Resource
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/rest/resource', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //set the profile id
  $profile = $request->get('source', 'profile_id');
  $request->setStage('profile_id', $profile);

  //call the job
  $this('event')->emit('rest-resource', $request, $response);
});
