<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Incept\Package\Handlebars\HandlebarsPackage;

$this
  //first register the package storm
  ->register('handlebars')
  //then load the package
  ->package('handlebars')
  //map handlebars package
  ->mapPackageMethods($this('resolver')->resolve(HandlebarsPackage::class))
  //use one global resolver
  ->setResolverHandler($this('resolver')->getResolverHandler());

//next add helpers
require_once __DIR__ . '/helpers.php';
