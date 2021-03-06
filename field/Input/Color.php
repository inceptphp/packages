<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Field\Input;

use Incept\Framework\Field\Input;
use Incept\Framework\Format\FormatTypes;

/**
 * Color Field
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class Color extends Input
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'color';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Color Field';

  /**
   * @const string INPUT_TYPE HTML input field type
   */
  const INPUT_TYPE = 'color';

  /**
   * @const array FORMATS List of possible formats
   */
  const FORMATS = [
    FormatTypes::TYPE_GENERAL,
    FormatTypes::TYPE_STRING,
    FormatTypes::TYPE_NUMBER,
    FormatTypes::TYPE_HTML,
    FormatTypes::TYPE_CUSTOM
  ];

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
    return is_null($value) || preg_match('/^[0-9a-fA-F]{6}$/', $value);
  }
}
