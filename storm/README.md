# Incept Storm

Integrates storm as an Incept Package

## Requires

 - [inceptphp/package](https://github.com/inceptphp/package)
 - [inceptphp/storm](https://github.com/inceptphp/storm)

## Install

```bash
composer install inceptphp/incept-storm
```

Once installed set up a PDO package.

```php
//setup PDO
incept('pdo')->register('custom', new PDO(...));
//setup storm and make this the default database
$this('storm')->load('custom');
```

Then you are all set.

```php
incept('event')->emit('storm-insert', ...);
```

More information can be found in the [Storm](https://github.com/inceptphp/storm)
library.
