<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Formatter\Number;

use Incept\Framework\Field\FieldRegistry;

use Incept\Framework\Format\AbstractFormatter;
use Incept\Framework\Format\FormatterInterface;
use Incept\Framework\Format\FormatTypes;

/**
 * Number Format
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class Number extends AbstractFormatter implements FormatterInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'number';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Number';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = FormatTypes::TYPE_NUMBER;

  /**
   * Renders the output format for object forms
   *
   * @param ?mixed  $value
   * @param ?string $name  name of the field formatting
   * @param ?array  $row   the row submitted with the value
   *
   * @return ?string
   */
  public function format(
    $value = null, 
    string $name = null, 
    array $row = []
  ): ?string
  {
    $parameters = $this->parameters;
    if (!isset($parameters[0])) {
      $parameters[0] = null;
    }

    if (!isset($parameters[1])) {
      $parameters[1] = null;
    }

    if (!isset($parameters[2])) {
      $parameters[2] = 0;
    }

    return (string) number_format(
      $value,
      $parameters[2],
      $parameters[1],
      $parameters[0]
    );
  }

  /**
   * When they choose this format in a schema form,
   * we need to know what parameters to ask them for
   *
   * @return array
   */
  public static function getConfigFieldset(): array
  {
    return [
      FieldRegistry::makeField('text')
        ->setName('{NAME}[parameters][0]')
        ->setAttributes([ 'placeholder' => 'Thousands separator eg. ,' ]),
      FieldRegistry::makeField('text')
        ->setName('{NAME}[parameters][1]')
        ->setAttributes([ 'placeholder' => 'Decimal separator eg. .' ]),
      FieldRegistry::makeField('number')
        ->setName('{NAME}[parameters][2]')
        ->setAttributes([
          'min' => 0,
          'max' => 10,
          'step' => 1,
          'placeholder' => 'Decimal range eg. 2'
        ])
    ];
  }
}
