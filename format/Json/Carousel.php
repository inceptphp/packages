<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Format\Json;

use Incept\Framework\Field\FieldRegistry;

use Incept\Framework\Format\AbstractFormatter;
use Incept\Framework\Format\FormatterInterface;
use Incept\Framework\Format\FormatTypes;

/**
 * Image Carousel
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class Carousel extends AbstractFormatter implements FormatterInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'carousel';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Image Carousel';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = FormatTypes::TYPE_JSON;

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
    $template = incept('handlebars')->compile(
      file_get_contents(__DIR__ . '/template/carousel.html')
    );

    return $template([
      'row' => $row,
      'value' => $value,
      'parameters' => $this->parameters
    ]);
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
        ->setAttributes([ 'placeholder' => 'Width' ]),
      FieldRegistry::makeField('text')
        ->setName('{NAME}[parameters][1]')
        ->setAttributes([ 'placeholder' => 'Height' ])
    ];
  }
}
