<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

require_once __DIR__ . '/package.php';

use Incept\Package\PackageMethods;

$this
  //then load the package
  ->package('inceptphp/packages')
  //map the package with the event package class methods
  ->mapPackageMethods($this('resolver')->resolve(PackageMethods::class, $this));
