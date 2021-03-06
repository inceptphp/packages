<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Field\Number;

/**
 * Rating Field
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class Rating extends Floating
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'rating';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Rating Field';

  /**
   * Renders the field for object forms
   *
   * @param ?mixed  $value
   * @param ?string $name  name of the column in the row
   * @param ?array  $row   the row submitted with the value
   *
   * @return ?string
   */
  public function render(
    $value = null,
    string $name = null,
    array $row = []
  ): ?string
  {
    $data = [
      'name' => $this->name,
      'value' => $value,
      'attributes' => $this->attributes,
      'options' => $this->options,
      'parameters' => $this->parameters
    ];

    $data['attributes']['type'] = static::INPUT_TYPE;
    $data['attributes']['min'] = 0;
    $data['attributes']['step'] = 0.5;

    $template = incept('handlebars')->compile(
      file_get_contents(__DIR__ . '/template/rating.html')
    );
    return $template($data);
  }
}
