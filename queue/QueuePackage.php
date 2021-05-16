<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Queue;

use Incept\Package\PackageTrait;
use Incept\Framework\Framework;

use Throwable;

if (!defined('WORKER_ID')) {
  define('WORKER_ID', md5(uniqid()));
}

/**
 * Queue package methods
 *
 * @vendor   Incept
 * @package  Package
 * @standard PSR-2
 */
class QueuePackage
{
  use PackageTrait;

  /**
   * @var *PackageHandler $handler
   */
  protected $handler;

  /**
   * Add handler for scope when routing
   *
   * @param *PackageHandler $handler
   */
  public function __construct(Framework $handler)
  {
    $this->handler = $handler;
  }

  /**
   */
  protected function log($message): QueuePackage
  {
    // notify its up
    $this->handler->log(sprintf('[%s] %s', substr(WORKER_ID, -6), $message));
    return $this;
  }

  /**
   * Queuer
   *
   * @param ?string $task The task name
   * @param ?array  $data Data to use for this task
   * @param ?string $queue The queue name
   *
   * @return ?AMQueue
   */
  public function queue(
    string $task = null,
    array $data = [],
    string $queue = null
  ) {
    $config = $this->handler->package('config')->get('services', 'amq-main');

    //if no config found or not active
    if (!$config || (isset($config['active']) && !$config['active'])) {
      return null;
    }

    if (is_null($queue)) {
      $queue = $config['name'];
    }

    $queue = new AMQueue(
      $config['host'],
      $queue,
      $config['user'],
      $config['pass'],
      $config['port']
    );

    if(!$task) {
      return $queue;
    }

    return $queue->send($task, $data);
  }

  /**
   * Worker using traditional means
   */
  public function work(string $queue = 'queue'): ?AMQueue
  {
    $config = $this->handler->package('config')->get('services', 'amq-main');

    //if no config found or not active
    if (!$config || (isset($config['active']) && !$config['active'])) {
      return null;
    }

    if (is_null($queue)) {
      $queue = $config['name'];
    }

    $queue = new AMQueue(
      $config['host'],
      $queue,
      $config['user'],
      $config['pass'],
      $config['port']
    );

    //run the consumer
    $package = $this;
    $queue->consume(function ($info) use ($package) {
      //check for body format
      if(!isset($info['task'])) {
        $package->log(' Invalid task format. Flushing.');
        return true;
      }

      // notify once a task is received
      $package->log(sprintf(' is received. (%s)', $info['priority']));

      if(!isset($info['data'])) {
        $info['data'] = [];
      }

      if(!empty($info['data'])) {
        $package->log(' Input:');
        $package->log(json_encode($info['data']));
      }

      $payload = $package->handler->makePayload();
      $payload['request']->setStage($info['data']);

      try {
        $package->handler->package('event')->emit(
          $info['task'],
          $payload['request'],
          $payload['response']
        );

        $package->log(' Event finished.');
      } catch (Throwable $e) {
        $package->log(sprintf(' Logic Error: %s. Aborting', $e->getMessage()));
        return false;
      }

      if ($payload['response']->isError()) {
        $package->log(sprintf(
          ' Response Error: %s. Aborting',
          $payload['response']->getMessage())
        );
        return false;
      }

      if($payload['response']->hasResults()) {
        $package->log(' Output:');
        $package->log(json_encode($payload['response']->getResults()));
      }

      $package->log(' has completed.');
      $package->handler->log('');
      return true;
    });
  }
}
