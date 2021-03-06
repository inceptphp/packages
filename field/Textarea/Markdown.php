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
 * Markdown Field
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class Markdown extends Textarea
{

  /**
   * @const string NAME Config name
   */
  const NAME = 'markdown';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Markdown Field';

  /**
   * @var array $attributes Hash of attributes to consider when rendering
   */
  protected $attributes = ['data-do' => 'markdown-editor'];

  /**
   * Sets the attributes that will be
   * considered when rendering the template
   *
   * @param *array $attributes
   *
   * @return FieldInterface
   */
  public function setAttributes(array $attributes): FieldInterface
  {
    $attributes['data-do'] = 'markdown-editor';
    return parent::setAttributes($attributes);
  }
}
