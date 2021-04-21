<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Field\Date;

use Incept\Framework\Field\FieldTypes;

use Incept\Package\Field\Input\Text;

/**
 * Week Field
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class Week extends Text
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'week';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Week Field';

  /**
   * @const string INPUT_TYPE HTML input field type
   */
  const INPUT_TYPE = 'week';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = FieldTypes::TYPE_DATE;
}
