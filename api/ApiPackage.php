<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Api;

use Incept\Package\PackageTrait;
use Incept\Framework\Framework;

/**
 * Role package methods
 *
 * @vendor   Incept
 * @package  Package
 * @standard PSR-2
 */
class ApiPackage
{
  use PackageTrait;

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
   * For Webhooks, checks if the list 1 exists in list 2
   *
   * @param *array $list1
   * @param *array $list2
   *
   * @return bool
   */
  public function validStage(array $list1, array $list2)
  {
    foreach ($list1 as $key => $value) {
      //if its not in list2
      if (!isset($list2[$key])) {
        return false;
      }

      //if value is not an array
      if (!is_array($value)) {
        if ($list2[$key] != $value) {
          return false;
        }

        continue;
      }

      //value is an array
      if(!$this->validStage($list1[$key], $list2[$key])) {
        return false;
      }
    }

    return true;
  }
}
