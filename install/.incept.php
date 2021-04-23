<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/package.php';

use Incept\Package\Install\InstallPackage;

$this
  //then load the package
  ->package('inceptphp/packages/install')
  //map the package with the event package class methods
  ->mapPackageMethods($this('resolver')->resolve(InstallPackage::class, $this));

$this->preprocess(include __DIR__ . '/boot.php');
