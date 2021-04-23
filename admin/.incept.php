<?php //-->
/**
 * This file is part of a Custom Project.
 */
require_once __DIR__ . '/controller/assets.php';
require_once __DIR__ . '/controller/collection.php';
require_once __DIR__ . '/controller/schema.php';
require_once __DIR__ . '/controller/field.php';
require_once __DIR__ . '/controller/fieldset.php';
require_once __DIR__ . '/controller/object.php';
require_once __DIR__ . '/controller/package.php';
require_once __DIR__ . '/controller/settings.php';
require_once __DIR__ . '/controller/language.php';
require_once __DIR__ . '/controller/view.php';

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/package.php';

use Incept\Package\Admin\AdminPackage;

$this
  //then load the package
  ->package('inceptphp/packages/admin')
  //map the package with the event package class methods
  ->mapPackageMethods($this('resolver')->resolve(AdminPackage::class, $this));

$this
  //Register a pseudo package admin
  ->register('admin')
  //then load the package
  ->package('admin')
  //map the package with the event package class methods
  ->mapPackageMethods($this('resolver')->resolve(AdminPackage::class, $this));

//set an error handler
$this->error([$this('admin'), 'error']);
