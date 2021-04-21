<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Validator\Type;

use Incept\Framework\Validation\AbstractValidator;
use Incept\Framework\Validation\ValidatorInterface;
use Incept\Framework\Validation\ValidationTypes;

/**
 * Hex Validator
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class ValidHex extends AbstractValidator implements ValidatorInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'hex';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Valid Hex';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = ValidationTypes::TYPE_TYPE;

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
    return preg_match('/^[0-9a-fA-F]{6}$/', $value);
  }
}
