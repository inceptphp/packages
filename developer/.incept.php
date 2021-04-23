<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

require_once __DIR__ . '/events/help.php';
require_once __DIR__ . '/events/install.php';
require_once __DIR__ . '/events/server.php';
require_once __DIR__ . '/events/update.php';
require_once __DIR__ . '/package.php';

use Incept\Package\Developer\DeveloperPackage;

$this
  //then load the package
  ->package('inceptphp/packages/developer')
  //map the package with the event package class methods
  ->mapPackageMethods($this('resolver')->resolve(DeveloperPackage::class, $this));
