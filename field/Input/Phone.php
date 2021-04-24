<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Field\Input;

use Incept\Framework\Field\FieldInterface;
use Incept\Framework\Format\FormatTypes;

/**
 * Phone Field
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class Phone extends Text
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'phone';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Phone Field';

  /**
   * @const string INPUT_TYPE HTML input field type
   */
  const INPUT_TYPE = 'tel';

  /**
   * @const array FORMATS List of possible formats
   */
  const FORMATS = [
    FormatTypes::TYPE_GENERAL,
    FormatTypes::TYPE_STRING,
    FormatTypes::TYPE_HTML,
    FormatTypes::TYPE_CUSTOM
  ];

  /**
   * @var array $attributes Hash of attributes to consider when rendering
   */
  protected $attributes = ['data-do' => 'phone-field'];

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
    $attributes['data-do'] = 'phone-field';
    return parent::setAttributes($attributes);
  }
}
