<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

require_once __DIR__ . '/controller/block.php';
require_once __DIR__ . '/controller/page.php';
require_once __DIR__ . '/controller/post.php';
require_once __DIR__ . '/events.php';
require_once __DIR__ . '/package.php';

use Incept\Package\Website\WebsitePackage;

$this
  //then load the package
  ->package('inceptphp/packages/website')
  //map the package with the event package class methods
  ->mapPackageMethods($this('resolver')->resolve(WebsitePackage::class, $this));

$this
  ->preprocess(include __DIR__ . '/boot/block.php')
  ->preprocess(include __DIR__ . '/boot/page.php');
