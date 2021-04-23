<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Field\Option;

use Incept\Framework\Field\FieldInterface;
use Incept\Framework\Format\FormatTypes;

/**
 * Country Drop Down Field
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class Country extends Select
{
  /**
   * @const bool HAS_OPTIONS Whether or not to show options fieldset
   * on the schema form if the field was chosen
   */
  const HAS_OPTIONS = false;

  /**
   * @const string NAME Config name
   */
  const NAME = 'country';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Country Field';

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
  protected $attributes = ['data-do' => 'country-dropdown'];

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
    parent::setAttributes($attributes);
    $this->attributes['data-do'] = 'country-dropdown';
    return $this;
  }
}
