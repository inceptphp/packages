<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Format\Json;

use Incept\Framework\Format\AbstractFormatter;
use Incept\Framework\Format\FormatterInterface;
use Incept\Framework\Format\FormatTypes;

/**
 * JSON Format
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class Json extends AbstractFormatter implements FormatterInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'json';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'JSON Pretty';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = FormatTypes::TYPE_JSON;

  /**
   * Renders the output format for object forms
   *
   * @param ?mixed  $value
   * @param ?string $name  name of the field formatting
   * @param ?array  $row   the row submitted with the value
   *
   * @return ?string
   */
  public function format(
    $value = null,
    string $name = null,
    array $row = []
  ): ?string
  {
    return json_encode($value, JSON_PRETTY_PRINT);
  }
}
