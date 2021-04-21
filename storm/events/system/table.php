<?php //-->

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

/**
 * Database insert Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-store-insert', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //just relay
  $this('event')->emit('storm-insert', $request, $response);
});

/**
 * Database delete Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-store-delete', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //just relay
  $this('event')->emit('storm-delete', $request, $response);
});

/**
 * Database search Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-store-search', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //just relay
  $this('event')->emit('storm-search', $request, $response);
});

/**
 * Database update Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('system-store-update', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //just relay
  $this('event')->emit('storm-update', $request, $response);
});
