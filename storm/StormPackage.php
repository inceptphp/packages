<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Storm;

use PDO;

use UGComponents\Package\Package;

use Storm\SqlFactory;

use Incept\Framework\Framework;
use Incept\Framework\Package\PDO\PDOPackage;

use Incept\Package\PackageTrait;

/**
 * Storm Package
 *
 * @vendor   Incept
 * @package  Package
 * @standard PSR-2
 */
class StormPackage
{
  use PackageTrait;

  /**
   * @var *array $connections
   */
  protected array $connections = [];

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
   * Disconnect a PDO resource
   *
   * @param *string $name
   *
   * @return StormPackage
   */
  public function disconnect(string $name): StormPackage
  {
    //if there's a connection
    if (isset($this->connections[$name])) {
      //disconnect the PDO
      $this->handler->package('pdo')->disconnect($name);
      //now disconnect it here as well
      $this->connections[$name] = null;
      unset($this->connections[$name]);
    }

    return $this;
  }

  /**
   * returns a PDO resource
   *
   * @param *string $name
   *
   * @return mixed
   */
  public function get(string $name)
  {
    if (!isset($connections[$name])) {
      //get the connection
      $connection = $this->handler->package('pdo')->get($name, false);
      //get the resolver
      $resolver = $this->handler->package('resolver');
      //resolve load the connection
      $connections[$name] = $resolver->resolveStatic(
        SqlFactory::class,
        'load',
        $connection
      );
    }

    return $connections[$name];
  }

  /**
   * Helper to transform schema field data to db schema info
   *
   * @param *array $field
   *
   * @return array
   */
  public function getFieldSchema(array $field): array
  {
    $schemas = [
      'json' => ['type' => 'JSON'],
      'string' => ['type' => 'VARCHAR', 'length' => 255],
      'text' => ['type' => 'TEXT'],
      'date' => ['type' => 'date'],
      'time' => ['type' => 'time'],
      'datetime' => ['type' => 'datetime'],
      'created' => ['type' => 'datetime'],
      'updated' => ['type' => 'datetime'],
      'week' => ['type' => 'INT', 'length' => 2, 'attribute' => 'unsigned'],
      'month' => ['type' => 'INT', 'length' => 2, 'attribute' => 'unsigned'],
      'active' => ['type' => 'INT', 'length' => 1, 'attribute' => 'unsigned'],
      'bool' => ['type' => 'INT', 'length' => 1, 'attribute' => 'unsigned'],
      'small' => ['type' => 'INT', 'length' => 1],
      'price' => ['type' => 'FLOAT', 'length' => '10,2']
    ];

    foreach ($schemas as $type => $schema) {
      if (in_array($type, $field['types'])) {
        return $schema;
      }
    }

    if (in_array('number', $field['types'])) {
      $length = [0, 0];

      $unsigned = isset($field['field']['attributes']['min'])
        && is_numeric($field['field']['attributes']['min'])
        && $field['field']['attributes']['min'] >= 0;

      foreach(['min', 'max', 'step'] as $attribute) {
        if (isset($field['field']['attributes'][$attribute])
          && is_numeric($field['field']['attributes'][$attribute])
        ) {
          $numbers = explode('.', (string) $field['field']['attributes'][$attribute]);
          if (strlen($numbers[0]) > $length[0]) {
            $length[0] = strlen($numbers[0]);
          }

          if (strlen($numbers[1]) > $length[1]) {
            $length[1] = strlen($numbers[1]);
          }
        }
      }

      if (!$length[0]) {
        $length[0] = 10;
      }

      if (!$length[1]) {
        if (in_array('float', $field['types'])) {
          $length[1] = 10;
        } else {
          unset($length[1]);
        }
      }

      if (count($length) == 2) {
        $schema = ['type' => 'FLOAT', 'length' => implode(',', $length)];
      } else {
        $schema = ['type' => 'INT', 'length' => (int) $length[0]];
      }

      if ($unsigned) {
        $schema['attribute'] = 'unsigned';
      }

      return $schema;
    }

    return ['type' => 'VARCHAR', 'length' => 255];
  }

  /**
   * Mutates to PDO using the given config
   *
   * @param *string $name
   *
   * @return PDOPackage
   */
  public function load(string $name): StormPackage
  {
    //load the storm package
    $package = $this->handler->package('storm');
    //re map to use the name's instance methods
    $package->mapPackageMethods($this->get($name));
    //use one global resolver
    $resolver = $this->handler->package('resolver');
    $package->setResolverHandler($resolver->getResolverHandler());
    return $this;
  }
}
