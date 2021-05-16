# Storm

Integrates [Storm](https://github.com/phpugph/storm) as an Incept Package

## Install

```bash
$ bin/incept inceptphp/packages/valid storm
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

## Usage

### Creating a Table

```php
incept('event')->emit('storm-create', [
  'table' => 'article',
  'primary' => 'article_title',
  'columns' => [
    'article_title' => ['type' => 'VARCHAR', 'length' => 255],
    'article_detail' => ['type' => 'TEXT', 'null' => true],
    'article_active' => ['type' => 'INT', 'length' => 10, 'default' => 1, 'attribute' => 'unsigned'],
    'article_created' => ['type' => 'DATE', 'required' => true],
    'article_updated' => ['type' => 'DATE', 'required' => true],
  ]
]);
```

```php
incept('event')->emit('storm-alter', [
  'table' => 'article',
  'columns' => [
    'article_title' => ['type' => 'VARCHAR', 'length' => 255],
    'article_detail' => ['type' => 'TEXT', 'null' => true],
    'article_active' => ['type' => 'INT', 'length' => 10, 'default' => 1, 'attribute' => 'unsigned'],
    'article_created' => ['type' => 'DATE', 'required' => true],
    'article_updated' => ['type' => 'DATE', 'required' => true],
  ]
]);
```
