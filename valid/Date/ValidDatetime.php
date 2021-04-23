<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Valid\Date;

use Incept\Framework\Validation\AbstractValidator;
use Incept\Framework\Validation\ValidatorInterface;
use Incept\Framework\Validation\ValidationTypes;

/**
 * Datetime Validator
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class ValidDatetime extends AbstractValidator implements ValidatorInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'datetime';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Valid Datetime';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = ValidationTypes::TYPE_DATE;

  /**
   * Renders the executes the validation for object forms
   *
   * @param ?mixed  $value
   * @param ?string $name  name of the field validating
   * @param ?array  $row   the row submitted with the value
   *
   * @return bool
   */
  public function valid($value = null, string $name = null, array $row = []): bool
  {
    return !preg_match('/^[0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/', $value);
  }
}
