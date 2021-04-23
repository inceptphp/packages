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
 * Small Number Field
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class Small extends Number
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'small';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Small Number Field';

  /**
   * @const array TYPES List of possible data types
   */
  const TYPES = [
    FieldTypes::TYPE_INT,
    FieldTypes::TYPE_NUMBER
  ];

  /**
   * @var array $attributes Hash of attributes to consider when rendering
   */
  protected $attributes = ['min' => 0, 'max' => 9, 'step' => 1];

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
    $attributes['min'] = 0;
    $attributes['max'] = 9;
    $attributes['step'] = 1;
    return parent::setAttributes($attributes);
  }
}
