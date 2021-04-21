<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Field\Input;

use Incept\Framework\Field\FieldInterface;

/**
 * Slug Field
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class Slug extends Text
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'slug';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Slug Field';

  /**
   * @var array $attributes Hash of attributes to consider when rendering
   */
  protected $attributes = ['data-do' => 'slugger'];

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
    $attributes['data-do'] = 'slugger';
    return parent::setAttributes($attributes);
  }
}
