<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package;

use Incept\Framework\Framework;

/**
 * General Package Methods
 *
 * @vendor   Incept
 * @package  Package
 * @standard PSR-2
 */
class PackageMethods
{
  use PackageTrait;

  /**
   * @var Framework $handler
   */
  protected $handler = null;

  /**
   * Add handler for scope when routing
   *
   * @param *PackageHandler $handler
   */
  public function __construct(Framework $handler)
  {
    $this->handler = $handler;
  }
}
