<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Field\Textarea;

use Incept\Framework\Field\FieldInterface;

/**
 * WYSIWYG Field
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class Wysiwyg extends Textarea
{

  /**
   * @const string NAME Config name
   */
  const NAME = 'wysiwyg';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'WYSIWYG Field';

  /**
   * @var array $attributes Hash of attributes to consider when rendering
   */
  protected $attributes = ['data-do' => 'wysiwyg-editor'];

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
    $attributes['data-do'] = 'wysiwyg-editor';
    return parent::setAttributes($attributes);
  }
}
