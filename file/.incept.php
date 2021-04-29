<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

require_once __DIR__ . '/events.php';
require_once __DIR__ . '/package.php';

use Incept\Package\File\FilePackage;

$this
  //then load the package
  ->package('inceptphp/packages/file')
  //map the package with the file package class methods
  ->mapPackageMethods($this('resolver')->resolve(FilePackage::class, $this));

$this
  //register a pseudo package
  ->register('file')
  //then load the package
  ->package('file')
  //map the package with the file package class methods
  ->mapPackageMethods($this('resolver')->resolve(FilePackage::class, $this));

$this->preprocess(function($request, $response) {
  $extensions = sprintf('%s/extensions.json', __DIR__);
  $json = file_get_contents($extensions);
  FilePackage::$extensions = json_decode($json, true);
});
