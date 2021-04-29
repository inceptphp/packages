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
 * Loads CSRF token in stage
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('csrf-load', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //render the key
  $key = md5(uniqid());
  if($request->hasSession('csrf')) {
    $key = $request->getSession('csrf');
  }

  $response->setSession('csrf', $key);
  $response->setResults('csrf', $key);
});

/**
 * Validates CSRF
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('csrf-validate', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $actual = $request->getStage('csrf');
  $expected = $request->getSession('csrf');

  //no longer needed
  $response->removeSession('csrf');

  if($actual !== $expected) {
    //prepare to error
    $message = $this('lang')->translate(
      'We prevented a potential attack on our servers coming from the request you just sent us.'
    );
    $response->setError(true, $message);
  }

  //it passed
});
