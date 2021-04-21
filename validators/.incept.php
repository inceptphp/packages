<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Incept\Framework\Validation\ValidatorRegistry;

//register validators
ValidatorRegistry::register(Incept\Package\Validator\General\Required::class);

ValidatorRegistry::register(Incept\Package\Validator\General\NotEmpty::class);

ValidatorRegistry::register(Incept\Package\Validator\General\NotEqual::class);

ValidatorRegistry::register(Incept\Package\Validator\General\ValidOption::class);

ValidatorRegistry::register(Incept\Package\Validator\General\Unique::class);

ValidatorRegistry::register(Incept\Package\Validator\Number\ValidNumber::class);

ValidatorRegistry::register(Incept\Package\Validator\Number\ValidFloat::class);

ValidatorRegistry::register(Incept\Package\Validator\Number\ValidPrice::class);

ValidatorRegistry::register(Incept\Package\Validator\Number\GreaterThan::class);

ValidatorRegistry::register(Incept\Package\Validator\Number\GreaterThanEqual::class);

ValidatorRegistry::register(Incept\Package\Validator\Number\LessThan::class);

ValidatorRegistry::register(Incept\Package\Validator\Number\LessThanEqual::class);

ValidatorRegistry::register(Incept\Package\Validator\String\CharGreaterThan::class);

ValidatorRegistry::register(Incept\Package\Validator\String\CharGreaterThanEqual::class);

ValidatorRegistry::register(Incept\Package\Validator\String\CharLessThan::class);

ValidatorRegistry::register(Incept\Package\Validator\String\CharLessThanEqual::class);

ValidatorRegistry::register(Incept\Package\Validator\String\WordGreaterThan::class);

ValidatorRegistry::register(Incept\Package\Validator\String\WordGreaterThanEqual::class);

ValidatorRegistry::register(Incept\Package\Validator\String\WordLessThan::class);

ValidatorRegistry::register(Incept\Package\Validator\String\WordLessThanEqual::class);

ValidatorRegistry::register(Incept\Package\Validator\Date\ValidDate::class);

ValidatorRegistry::register(Incept\Package\Validator\Date\ValidTime::class);

ValidatorRegistry::register(Incept\Package\Validator\Date\ValidDatetime::class);

ValidatorRegistry::register(Incept\Package\Validator\Date\ValidPastDate::class);

ValidatorRegistry::register(Incept\Package\Validator\Date\ValidPresentDate::class);

ValidatorRegistry::register(Incept\Package\Validator\Date\ValidFutureDate::class);

ValidatorRegistry::register(Incept\Package\Validator\Type\ValidEmail::class);

ValidatorRegistry::register(Incept\Package\Validator\Type\ValidUrl::class);

ValidatorRegistry::register(Incept\Package\Validator\Type\ValidHex::class);

ValidatorRegistry::register(Incept\Package\Validator\Type\ValidCC::class);

ValidatorRegistry::register(Incept\Package\Validator\Custom\ValidExpression::class);
