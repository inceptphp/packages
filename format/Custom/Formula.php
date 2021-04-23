<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Formatter\Custom;

use Incept\Framework\Field\FieldRegistry;

use Incept\Framework\Format\AbstractFormatter;
use Incept\Framework\Format\FormatterInterface;
use Incept\Framework\Format\FormatTypes;

/**
 * Formula Format
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class Formula extends AbstractFormatter implements FormatterInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'formula';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Formula';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = FormatTypes::TYPE_CUSTOM;

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
    $template = incept('handlebars')->compile($this->parameters[0]);
    $formula = $template($row);
    $expression = sprintf('return %s ;', $formula);

    try {
      $value = @eval($expression);
    } catch (Throwable $e) {
      return 'Parse Error';
    }

    return number_format((float) $value, 2);
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
      FieldRegistry::makeField('textarea')
        ->setName('{NAME}[parameters][0]')
        ->setAttributes([ 'placeholder' => 'eg. ( {{salary}} * {{tax}} ) / 10' ])
    ];
  }
}
