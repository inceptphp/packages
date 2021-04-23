<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

require_once __DIR__ . '/package.php';

use Incept\Package\Profile\ProfilePackage;

$this
  //then load the package
  ->package('inceptphp/packages/profile')
  //map the package with the event package class methods
  ->mapPackageMethods($this('resolver')->resolve(ProfilePackage::class, $this));
