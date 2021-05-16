<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Field\Input;

/**
 * Password Field
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class Password extends Text
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'password';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Password Field';

  /**
   * @const string INPUT_TYPE HTML input field type
   */
  const INPUT_TYPE = 'password';

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
      return $value;
    }

    return password_hash($value, PASSWORD_DEFAULT);
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
  ): ?string {
    $data = [
      'name' => $this->name,
      'value' => $value,
      'attributes' => $this->attributes,
      'options' => $this->options,
      'parameters' => $this->parameters
    ];

    if (static::INPUT_TYPE) {
      $data['attributes']['type'] = static::INPUT_TYPE;
    }

    $template = incept('handlebars')->compile(
      file_get_contents(__DIR__ . '/template/password.html')
    );
    return $template($data);
  }
}
