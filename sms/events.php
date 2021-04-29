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
 * Send OTP
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this->on('sms-otp-send', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //if error
  if ($response->isError()) {
    //do nothing
    return;
  }

  //if no to
  if (!$request->getStage('to')) {
    //send back an error
    return $response
      ->setError(true, 'Invalid parameters')
      ->invalidate('to', 'To is required');
  }

  //determine pin code
  $pin = rand(1111, 9999);
  //set the body
  $body = $this('lang')->translate('Verification Code is %s', $pin);

  //set event
  $request->setStage(0, 'sms-send');
  //set subject
  $request->setStage('message', $body);

  //----------------------------//
  // 3. Process Data
  $this('event')->emit('queue', $request, $response);

  //if we werent able to queue
  if ($response->isError()) {
    $data = $request->getStage();
    //send manually after the connection
    $this->postprocess(function ($request, $response) use ($data) {
      $this('event')->call('sms-send', $data);
    });
  }

  //set the otp pin
  $response
    ->setError(false)
    ->setResults('otp', $pin);
});
