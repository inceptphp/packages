# SMS

SMS integration abstract. Before expecting this working you need to define an `sms-send` event.

```php
$this('event')->on('sms-send', function ($req, $res) {
  $to = $req->getStage('to');
  $message = $req->geStage('message');

  ...
});
```

## Install

If you already installed Incept, you may not need to install this because it
should be already included.

```
$ bin/incept inceptphp/packages/sms install
```
