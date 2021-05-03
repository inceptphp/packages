<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

require_once __DIR__ . '/app/controller.php';
require_once __DIR__ . '/dialog/controller.php';
require_once __DIR__ . '/rest/events.php';
require_once __DIR__ . '/rest/controller.php';
require_once __DIR__ . '/webhook/events.php';

require_once __DIR__ . '/developer/controller/assets.php';
require_once __DIR__ . '/developer/controller/docs.php';
require_once __DIR__ . '/package.php';

use Incept\Package\Api\ApiPackage;

$this
  //then load the package
  ->package('inceptphp/packages/api')
  //map the package with the event package class methods
  ->mapPackageMethods($this('resolver')->resolve(ApiPackage::class, $this));

//then preprocess the checks
$this
  ->preprocess(include __DIR__ . '/boot/body.php')
  ->preprocess(include __DIR__ . '/boot/rest.php')
  ->preprocess(include __DIR__ . '/boot/webhooks.php');
