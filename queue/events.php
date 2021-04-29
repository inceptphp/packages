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
 * CLI queue - bin/incept queue auth-verify auth_email=<email>
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('queue', function(
  RequestInterface $request,
  ResponseInterface $response
) {
  $data = $request->getStage();

  if (!isset($data[0])) {
    return $response->setError(true, 'Missing event name');
  }

  $event = array_shift($data);

  $priority = 0;
  if (isset($data['priority'])) {
    $priority = $data['priority'];
    unset($data['priority']);
  }

  $delay = 0;
  if (isset($data['delay'])) {
    $delay = $data['delay'];
    unset($data['delay']);
  }

  $retry = 0;
  if (isset($data['retry'])) {
    $delay = $data['retry'];
    unset($data['retry']);
  }

  $queue = $this('queue')->queue();

  if (!$queue) {
    return $response->setError(true, 'Queue is not setup');
  }

  $sent = $queue
    ->setDelay($delay)
    ->setPriority($priority)
    ->setRetry($retry)
    ->send($event, $data);

  if (!$sent) {
    return $response->setError(true, 'Did not queue');
  }

  $response->setError(false);
});


/**
 * CLI worker - bin/incept work
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('work', function(
  RequestInterface $request,
  ResponseInterface $response
) {
  $config = $this('config')->get('services', 'amq-main');
  //if no config
  if (!$config || (isset($config['active']) && !$config['active'])) {
    //do nothing
    return;
  }
  //get the queue name
  $name = $config['name'] ?? 'queue';
  if ($request->hasStage(0)) {
    $name = $request->getStage(0);
  } else if ($request->hasStage('name')) {
    $name = $request->getStage('name');
  }

  $verbose = false;
  if($request->hasStage('v') || $request->hasStage('verbose')) {
    $verbose = true;
    $this->addLogger(function($message) {
      echo $message . PHP_EOL;
    });
  }

  $this('queue')->work($name);
});
