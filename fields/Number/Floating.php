<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Field\Number;

use Incept\Framework\Field\FieldInterface;
use Incept\Framework\Field\FieldTypes;

/**
 * Float Field
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class Floating extends Number
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'float';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Float Field';

  /**
   * @const array TYPES List of possible data types
   */
  const TYPES = [
    FieldTypes::TYPE_FLOAT,
    FieldTypes::TYPE_NUMBER
  ];

  /**
   * @var array $attributes Hash of attributes to consider when rendering
   */
  protected $attributes = [ 'step' => 0.0000000001 ];

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
    return (float) sprintf('%.10F', $value);
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
    if (isset($attributes['step'])) {
      $attributes['step'] = 0.0000000001;
    }

    return parent::setAttributes($attributes);
  }
}
