<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Field\Number;

use Incept\Framework\Field\FieldInterface;

/**
 * Knob Field
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class Knob extends Number
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'knob';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Knob Field';

  /**
   * @var array $attributes Hash of attributes to consider when rendering
   */
  protected $attributes = [ 'data-do' => 'knob-field' ];

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
    $attributes['data-do'] = 'knob-field';
    return parent::setAttributes($attributes);
  }
}
