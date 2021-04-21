<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Field\Input;

/**
 * Text Field
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class Text extends Input
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'text';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Text Field';

  /**
   * @const string INPUT_TYPE HTML input field type
   */
  const INPUT_TYPE = 'text';
}
