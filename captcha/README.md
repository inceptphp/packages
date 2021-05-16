# Google reCaptcha

[Google reCaptcha](https://www.google.com/recaptcha/about/) integration

## Install

If you already installed Incept, you may not need to install this because it
should be already included.

```
$ bin/incept inceptphp/packages/captcha install
```

Go to [https://www.google.com/recaptcha/](https://www.google.com/recaptcha/) and
register for a token and secret.

Open `/config/services.php` and add

```php
'captcha-main' => array(
    'token' => '<Google Token>',
    'secret' => '<Google Secret>'
),
```

## Usage

In any of your routes add the following code.

```php
incept('event')->emit('captcha-load', $req, $res);
```

The CSRF token will be found in `$req->getStage('captcha')`. In your form
template, be sure to add this key in a hidden field like the following.

```html
<script src="https://www.google.com/recaptcha/api.js"></script>
<div class="g-recaptcha" data-sitekey="{{captcha}}"></div>
```

When validating this form in a route you can use the following

```php
incept('event')->emit('captcha-validate', $req, $res);
```

If there is an error, it will be found in the response error object message.
You can check this using the following.

```php
if($res->isError()) {
    $message = $res->getMessage();
    //report the error
}
```
