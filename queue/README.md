# Queue

ActiveMQ prototcol integration

## Install

If you already installed Incept, you may not need to install this because it
should be already included.

```php
$ bin/incept inceptphp/packages/queue install
```

## Usage

Basic Queuing

```php
incept('queue')->queue('event name', [
  'foo' => bar
], '?queue name');
```

Advanced Queueing

```
incept('queue')
    ->queue()
    ->setDelay(*string $delay)
    ->setPriority(*int $priority)
    ->setQueue(*string $queueName)
    ->setRetry(*int $retry)
    ->send(*string $task, ?array $data, ?string $queueName);
```

## Command Line

You can queue events via command line like the following example.

```bash
$ bin/incept queue event-name foo=bar zoo=foo
```

To start a worker use any of the following commands.

```bash
$ bin/incept work
```
