<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Field\Json;

/**
 * Lat/Lng Fieldset
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class LatLng extends TextList
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'latlng';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Lat/Lng Fieldset';

  /**
   * Prepares the value for some sort of insertion
   *
   * @param ?mixed  $value
   * @param ?string $name  name of the column in the row
   * @param ?array  $row   the row submitted with the value
   *
   * @return ?scalar
   */
  public function prepare($value = null, string $name = null, array $row = [])
  {
    if (is_null($value)) {
      return null;
    }

    if (!is_numeric($value[0])) {
      $value[0] = 0;
    }

    if (!is_numeric($value[1])) {
      $value[1] = 0;
    }

    $value[0] = sprintf('%.8F', $value[0]);
    $value[1] = sprintf('%.8F', $value[1]);

    return json_encode($value);
  }

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
    $template = incept('handlebars')->compile(
      file_get_contents(__DIR__ . '/template/latlng.html')
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
