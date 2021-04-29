<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

require_once __DIR__ . '/controller/2fa.php';
require_once __DIR__ . '/controller/admin.php';
require_once __DIR__ . '/controller/forgot.php';
require_once __DIR__ . '/controller/oauth.php';
require_once __DIR__ . '/controller/password.php';
require_once __DIR__ . '/controller/signin.php';
require_once __DIR__ . '/controller/signout.php';
require_once __DIR__ . '/controller/signup.php';
require_once __DIR__ . '/controller/verify.php';
require_once __DIR__ . '/events.php';
require_once __DIR__ . '/package.php';

use Incept\Package\Auth\AuthPackage;

$this
  //then load the package
  ->package('inceptphp/packages/auth')
  //map the package with the event package class methods
  ->mapPackageMethods($this('resolver')->resolve(AuthPackage::class, $this));

$this
  //Register a pseudo package auth
  ->register('auth')
  //then load the package
  ->package('auth')
  //map the package with the event package class methods
  ->mapPackageMethods($this('resolver')->resolve(AuthPackage::class, $this));
