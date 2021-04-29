<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

require_once __DIR__ . '/events/storm/schema.php';
require_once __DIR__ . '/events/storm/table.php';
require_once __DIR__ . '/events/system/schema.php';
require_once __DIR__ . '/events/system/table.php';
require_once __DIR__ . '/package.php';
require_once __DIR__ . '/package/build.php';
require_once __DIR__ . '/package/flush.php';
require_once __DIR__ . '/package/install.php';

use Incept\Package\Storm\StormPackage;

//Register a pseudo package storm
$this
  //Register a pseudo package storm
  ->register('storm')
  //then load the package
  ->package('storm')
  //map the package with the event package class methods
  ->mapPackageMethods($this('resolver')->resolve(StormPackage::class, $this));

//then preprocess the loader
$this->preprocess(include __DIR__ . '/boot.php');
