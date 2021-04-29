<?php //-->
/**
 * This file is part of a package designed for the CradlePHP Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Packages\Queue\Service;

use Exception;
use Throwable;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AbstractConnection as Resource;
use PhpAmqpLib\Connection\AMQPLazyConnection as Connection;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;
use PhpAmqpLib\Wire\AMQPTable;

/**
 * AMQ Service
 *
 * @vendor   Incept
 * @package  Package
 * @standard PSR-2
 */
class AMQueue
{
  /**
   * @var string $delay
   */
  protected $delay = 0;

  /**
   * @var string $host Queue host
   */
  protected $host = 'localhost';

  /**
   * @var ?string $name Queue name
   */
  protected $name = null;

  /**
   * @var ?string $pass Queue password
   */
  protected $pass = null;

  /**
   * @var ?int $port Queue password
   */
  protected $port = null;

  /**
   * @var string $priority
   */
  protected $priority = 0;

  /**
   * @var [RESOURCE] $resource
   */
  protected $resource = null;

  /**
   * @var string $retry
   */
  protected $retry = 0;

  /**
   * @var ?string $user Queue user name
   */
  protected $user = null;

  /**
   * Construct: Store connection information
   *
   * @param ?string $host Queue host
   * @param ?string $name Queue name
   * @param ?string $user Queue user name
   * @param ?string $pass Queue password
   * @param ?number $port Queue port
   */
  public function __construct(
    string $host = '127.0.0.1',
    string $name = 'queue',
    string $user = 'guest',
    string $pass = 'guest',
    int $port = 5672
  ) {
    $this->host = $host;
    $this->name = $name;
    $this->user = $user;
    $this->pass = $pass;
    $this->port = $port;
  }

  /**
   * Connects to the queue
   *
   * @return AMQueue
   */
  public function connect(Connection $resource = null): AMQueue
  {
    //if there's a resource
    if ($resource instanceof Connection) {
      $this->resource = $resource;
      return $this;
    }

    //only connect if not connected yet
    if ($this->resource instanceof Connection) {
      return $this;
    }

    $this->resource = new Connection(
      $this->host,
      $this->port,
      $this->user,
      $this->pass
    );

    return $this;
  }

  /**
   * Consumes Message
   *
   * @return bool
   */
  public function consume(callable $callback): bool
  {
    try {
      $resource = $this->getConnection();
      $channel = $resource->channel();
    } catch(Throwable $e) {
      return false;
    }

    // worker consuming tasks from queue
    $channel->basic_qos(null, 1, null);

    $consumer = function($message) use ($resource, $channel, $callback) {
      $info = json_decode($message->body, true);
      $serial = base64_encode(json_encode([
        'queue' => $info['queue'],
        'task' => $info['task'],
        'delay' => $info['delay'],
        'data' => $info['data']
      ]));

      try {
        $results = call_user_func($callback, $info);
      } catch (Throwable $e) {
        $results = false;
      }

      //if it failed and theres a retry
      if(!$results
        && isset(
          $info['queue'],
          $info['task'],
          $info['retry'],
          $info['priority'],
          $info['delay'],
          $info['data']
        )
        && $info['retry']
      ) {
        //try to requeue
        self::loadResource($resource)
          ->setDelay($info['delay'])
          ->setPriority($info['priority'])
          ->setQueue($info['queue'])
          ->setRetry(--$info['retry'])
          ->send($info['task'], $info['data']);
      }

      //remove from queue
      $channel = $message->delivery_info['channel'];
      $channel->basic_nack($message->delivery_info['delivery_tag']);
    };

    // now we need to catch the channel exception
    // when task does not exists in our queue
    try {
      // comsume messages on queue
      $channel->basic_consume(
        $this->name,
        '',
        false,
        false,
        false,
        false,
        $consumer->bindTo($this, get_class($this))
      );
    } catch (AMQPProtocolChannelException $e) {
      return false;
    } catch(Throwable $e) {
      return false;
    }

    while (count($channel->callbacks)) {
      $channel->wait();
    }

    return true;
  }

  /**
   * Connects to the queue
   *
   * @return Connection
   */
  public function getConnection(): Connection
  {
    //only connect if not connected yet
    if (!($this->resource instanceof Connection)) {
      $this->connect();
    }

    return $this->resource;
  }

  /**
   * Adaptor used to force a connection to the handler
   *
   * @param *Connection $resource
   *
   * @return AMQueue
   */
  public static function loadResource(Connection $resource): AMQueue
  {
    $reflection = new ReflectionClass(static::class);
    $instance = $reflection->newInstanceWithoutConstructor();
    return $instance->connect($connection);
  }

  /**
   * Sends Message
   *
   * @param *string $task
   * @param array   $data
   *
   * @return bool
   */
  public function send(
    string $task,
    array $data = [],
    string $queue = null
  ): bool {
    try {
      $channel = $this->getConnection()->channel();
    } catch(Throwable $e) {
      return false;
    }

    if (!$queue) {
      $queue = $this->name;
    }

    $exchange = sprintf(
      '%s-xchnge',
      $queue
    );

    $delayName = sprintf(
      '%s-delay-%s',
      $queue,
      $this->delay / 1000
    );

    $delayExchange = sprintf(
      '%s-xchnge-delay-%s',
      $this->name,
      $this->delay / 1000
    );

    $info = [
      'queue' => $queue,
      'task' => $task,
      'retry' => $this->retry,
      'priority' => $this->priority,
      'delay' => $this->delay,
      'data' => $data
    ];

    $serial = base64_encode(json_encode([
      'queue' => $info['queue'],
      'task' => $info['task'],
      'delay' => $info['delay'],
      'data' => $info['data']
    ]));

    $options = ['delivery_mode' => 2];

    if($this->priority) {
      $options['priority'] = $this->priority;
    }

    //declare the queue
    $channel->queue_declare(
      $queue,
      false,
      true,
      false,
      false,
      false,
      ['x-max-priority' => ['I', 100]]
    );

    $channel->exchange_declare($exchange, 'direct');
    $channel->queue_bind($queue, $exchange);
    $message = new AMQPMessage(json_encode($info), $options);

    // if no delay queue it now
    if (!$this->delay) {
      $channel->basic_publish($message, $exchange);
      return true;
    }

    $channel->queue_declare(
        $delayName,
        false,
        false,
        false,
        false,
        false,
        [
          'x-max-priority' => ['I', 100],
          'x-message-ttl' => ['I', $this->delay],
          // after message expiration in delay queue, move message to the right.now.queue
          'x-dead-letter-exchange' => ['S', $exchange]
        ]
    );

    $channel->exchange_declare(
      $delayExchange,
      'x-delayed-message',
      false,
      false,
      false,
      false,
      false,
      new AMQPTable([
         'x-delayed-type' => 'fanout'
      ])
    );

    $channel->queue_bind($delayName, $delayExchange);
    $channel->basic_publish($message, $delayExchange);

    return true;
  }

  /**
   * Set Delay
   *
   * @param *int $delay
   *
   * @return AMQueue
   */
  public function setDelay(int $delay): AMQueue
  {
    //max is around 2147480
    $this->delay = min(2147480, $delay) * 1000;
    return $this;
  }

  /**
   * Set Priority
   *
   * @param *int $priority
   *
   * @return AMQueue
   */
  public function setPriority(int $priority): AMQueue
  {
    $this->priority = $priority;
    return $this;
  }

  /**
   * Set Retries
   *
   * @param *int $retry
   *
   * @return AMQueue
   */
  public function setRetry(int $retry): AMQueue
  {
    $this->retry = $retry;
    return $this;
  }
}
