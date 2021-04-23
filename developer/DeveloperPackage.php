<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Developer;

use Incept\Package\PackageTrait;
use Incept\Framework\Framework;

/**
 * Developer Package Methods
 *
 * @vendor   Incept
 * @package  Package
 * @standard PSR-2
 */
class DeveloperPackage
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
}
