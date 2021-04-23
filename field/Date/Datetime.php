<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Field\Date;

use Incept\Framework\Field\FieldTypes;
use Incept\Framework\Field\FieldInterface;
use Incept\Framework\Format\FormatTypes;

use Incept\Package\Field\Input\Input;

/**
 * Datetime Field
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class Datetime extends Input
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'datetime';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Datetime Field';

  /**
   * @const string INPUT_TYPE HTML input field type
   */
  const INPUT_TYPE = 'datetime';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = FieldTypes::TYPE_DATE;

  /**
   * @const array TYPES List of possible data types
   */
  const TYPES = [
    FieldTypes::TYPE_DATETIME
  ];

  /**
   * @const array FORMATS List of possible formats
   */
  const FORMATS = [
    FormatTypes::TYPE_GENERAL,
    FormatTypes::TYPE_DATE,
    FormatTypes::TYPE_CUSTOM
  ];

  /**
   * @var array $attributes Hash of attributes to consider when rendering
   */
  protected $attributes = ['data-do' => 'datetime-field'];

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
    if (!$value) {
      return null;
    }

    return date('Y-m-d H:i:s', strtotime($value));
  }

  /**
   * Renders the field for filter forms
   *
   * @param ?mixed  $value
   * @param ?string $name  name of the column in the row
   *
   * @return ?string
   */
  public function renderFilter($value = null, string $name = null): ?string {
    $template = incept('handlebars')->compile(
      file_get_contents(__DIR__ . '/template/filter/datetime.html')
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
   * Sets the attributes that will be
   * considered when rendering the template
   *
   * @param *array $attributes
   *
   * @return FieldConfigInterface
   */
  public function setAttributes(array $attributes): FieldInterface
  {
    $attributes['data-do'] = 'datetime-field';
    return parent::setAttributes($attributes);
  }

  /**
   * Validation check
   *
   * @param ?mixed  $value
   * @param ?string $name  name of the column in the row
   * @param ?array  $row   the row submitted with the value
   *
   * @return bool
   */
  public function valid(
    $value = null,
    string $name = null,
    array $row = []
  ): bool
  {
    return is_null($value) || strtotime($value) !== false;
  }
}
