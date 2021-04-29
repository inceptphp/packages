<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/package.php';

use Incept\Package\Role\RolePackage;

$this
  //then load the package
  ->package('inceptphp/packages/role')
  //map the package with the event package class methods
  ->mapPackageMethods($this('resolver')->resolve(RolePackage::class, $this));

//set an error handler
$this->error([$this('inceptphp/packages/role'), 'error']);

//then preprocess the checks
$this
  ->preprocess(include __DIR__ . '/boot/session.php')
  ->preprocess(include __DIR__ . '/boot/permitted.php')
  ->preprocess(include __DIR__ . '/boot/menu.php');
