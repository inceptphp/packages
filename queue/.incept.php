<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

require_once __DIR__ . '/events.php';
require_once __DIR__ . '/package.php';

use Incept\Package\Queue\QueuePackage;

$this
  //then load the package
  ->package('inceptphp/packages/queue')
  //map the package with the csrf package class methods
  ->mapPackageMethods($this('resolver')->resolve(QueuePackage::class, $this));

$this
  //register a pseudo package
  ->register('queue')
  //then load the package
  ->package('queue')
  //map the package with the csrf package class methods
  ->mapPackageMethods($this('resolver')->resolve(QueuePackage::class, $this));
