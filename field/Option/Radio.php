<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Field\Option;

/**
 * Radio Fieldset
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class Radio extends Select
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'radio';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Radio Fieldset';

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
  ): ?string {
    $template = incept('handlebars')->compile(
      file_get_contents(__DIR__ . '/template/field/radio.html')
    );
    return $template([
      'name' => $this->name,
      'value' => $value,
      'attributes' => $this->attributes,
      'options' => $this->options,
      'parameters' => $this->parameters
    ]);
  }

  /**
   * Renders the field for filter forms
   *
   * @param ?mixed  $value
   * @param ?string $name  name of the column in the row
   *
   * @return ?string
   */
  public function renderFilter($value = null, string $name = null): ?string
  {
    $template = incept('handlebars')->compile(
      file_get_contents(__DIR__ . '/template/filter/radio.html')
    );

    return $template([
      'name' => $this->name,
      'value' => $value,
      'attributes' => $this->attributes,
      'options' => $this->options,
      'parameters' => $this->parameters
    ]);
  }
}
