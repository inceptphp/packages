<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Valid\Number;

use Incept\Framework\Field\FieldRegistry;

use Incept\Framework\Validation\AbstractValidator;
use Incept\Framework\Validation\ValidatorInterface;
use Incept\Framework\Validation\ValidationTypes;

/**
 * Number Greater Than Equal Validator
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class GreaterThanEqual extends AbstractValidator implements ValidatorInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'gte';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Greater Than Equal';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = ValidationTypes::TYPE_NUMBER;

  /**
   * When they choose this validator in a schema form,
   * we need to know what parameters to ask them for
   *
   * @return array
   */
  public static function getConfigFieldset(): array
  {
    return [
      FieldRegistry::makeField('number')
        ->setName('{NAME}[parameters][0]')
        ->setAttributes([
          'placeholder' => 'Enter Number',
          'required' => 'required'
        ])
    ];
  }

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
    return isset($this->parameters[0]) && $value >= $this->parameters[0];
  }
}
