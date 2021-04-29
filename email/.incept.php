<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/events.php';
require_once __DIR__ . '/package.php';

use Incept\Package\Email\EmailPackage;

$this
  //then load the package
  ->package('inceptphp/packages/email')
  //map the package with the event package class methods
  ->mapPackageMethods($this('resolver')->resolve(EmailPackage::class, $this));
