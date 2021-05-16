# SMS

Email integration

## Install

If you already installed Incept, you may not need to install this because it
should be already included.

```
$ bin/incept inceptphp/packages/email install
```

## Usage

Sending basic mail

```php
incept('event')->call('email-send', [
  'to' => [
    ['name' => 'John Doe', 'address' => 'john@doe.com'],
    'jane@doe.com'
  ],
  'cc' => 'james@doe.com',
  'bcc' => 'jack@doe.com',
  'subject' => 'Hello World',
  'text' => 'Thanks for playing',
  'html' => '<p>Thanks for playing</p>',
  'attachments' => [
    '/path/to/file.pdf'
  ]
]);
```

Send a one time pin

```php
incept('event')->call('email-otp-send', [
  'to' => 'jane@doe.com'
], $res);

$pincode = $res->getResults('otp');
//save pincode to session ...
```
