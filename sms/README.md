# SMS

SMS integration abstract.

## Install

If you already installed Incept, you may not need to install this because it
should be already included.

```
$ bin/incept inceptphp/packages/sms install
```

## Usage

Before expecting this working you need to define an `sms-send` event.

```php
$this('event')->on('sms-send', function ($req, $res) {
  $to = $req->getStage('to');
  $message = $req->geStage('message');

  ...
});
```

Send a one time pin

```php
incept('event')->call('sms-otp-send', [
  'to' => '+14105552424'
], $res);

$pincode = $res->getResults('otp');
//save pincode to session ...
```
