<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

require_once __DIR__ . '/package.php';

use Incept\Framework\Validation\ValidatorRegistry;
use Incept\Package\Valid\ValidPackage;

$this
  //then load the package
  ->package('inceptphp/packages/valid')
  //map the package with the event package class methods
  ->mapPackageMethods($this('resolver')->resolve(ValidPackage::class, $this));

//register validators
ValidatorRegistry::register(Incept\Package\Valid\General\Required::class);

ValidatorRegistry::register(Incept\Package\Valid\General\NotEmpty::class);

ValidatorRegistry::register(Incept\Package\Valid\General\NotEqual::class);

ValidatorRegistry::register(Incept\Package\Valid\General\ValidOption::class);

ValidatorRegistry::register(Incept\Package\Valid\General\Unique::class);

ValidatorRegistry::register(Incept\Package\Valid\Number\ValidNumber::class);

ValidatorRegistry::register(Incept\Package\Valid\Number\ValidFloat::class);

ValidatorRegistry::register(Incept\Package\Valid\Number\ValidPrice::class);

ValidatorRegistry::register(Incept\Package\Valid\Number\GreaterThan::class);

ValidatorRegistry::register(Incept\Package\Valid\Number\GreaterThanEqual::class);

ValidatorRegistry::register(Incept\Package\Valid\Number\LessThan::class);

ValidatorRegistry::register(Incept\Package\Valid\Number\LessThanEqual::class);

ValidatorRegistry::register(Incept\Package\Valid\String\CharGreaterThan::class);

ValidatorRegistry::register(Incept\Package\Valid\String\CharGreaterThanEqual::class);

ValidatorRegistry::register(Incept\Package\Valid\String\CharLessThan::class);

ValidatorRegistry::register(Incept\Package\Valid\String\CharLessThanEqual::class);

ValidatorRegistry::register(Incept\Package\Valid\String\WordGreaterThan::class);

ValidatorRegistry::register(Incept\Package\Valid\String\WordGreaterThanEqual::class);

ValidatorRegistry::register(Incept\Package\Valid\String\WordLessThan::class);

ValidatorRegistry::register(Incept\Package\Valid\String\WordLessThanEqual::class);

ValidatorRegistry::register(Incept\Package\Valid\Date\ValidDate::class);

ValidatorRegistry::register(Incept\Package\Valid\Date\ValidTime::class);

ValidatorRegistry::register(Incept\Package\Valid\Date\ValidDatetime::class);

ValidatorRegistry::register(Incept\Package\Valid\Date\ValidPastDate::class);

ValidatorRegistry::register(Incept\Package\Valid\Date\ValidPresentDate::class);

ValidatorRegistry::register(Incept\Package\Valid\Date\ValidFutureDate::class);

ValidatorRegistry::register(Incept\Package\Valid\Type\ValidEmail::class);

ValidatorRegistry::register(Incept\Package\Valid\Type\ValidUrl::class);

ValidatorRegistry::register(Incept\Package\Valid\Type\ValidHex::class);

ValidatorRegistry::register(Incept\Package\Valid\Type\ValidCC::class);

ValidatorRegistry::register(Incept\Package\Valid\Custom\ValidExpression::class);
