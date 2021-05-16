# CSRF

[CSRF](https://owasp.org/www-community/attacks/csrf) protection

## Install

If you already installed Incept, you may not need to install this because it
should be already included.

```
$ bin/incept inceptphp/packages/csrf install
```

## Usage

In any of your routes add the following code.

```php
incept('event')->emit('csrf-load', $req, $res);
```

The CSRF token will be found in `$req->getStage('csrf')`. In your form
template, be sure to add this key in a hidden field like the following.

```html
<input name="csrf" value="{{csrf}}" />
```

When validating this form in a route you can use the following

```php
incept('event')->emit('csrf-validate', $req, $res);
```

If there is an error, it will be found in the response error object message.
You can check this using the following.

```php
if($res->isError()) {
    $message = $res->getMessage();
    //report the error
}
```
