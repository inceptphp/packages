<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Field\File;

use Incept\Framework\Field\FieldInterface;

/**
 * Image Field
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class Image extends File
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'image';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Image Field';

  /**
   * @var array $attributes Hash of attributes to consider when rendering
   */
  protected $attributes = [
    'data-accept' => 'image/png,image/jpg,image/jpeg,image/gif'
  ];

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
    $attributes['data-accept'] = 'image/png,image/jpg,image/jpeg,image/gif';
    return parent::setAttributes($attributes);
  }
}
