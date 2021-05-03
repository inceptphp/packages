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

use Incept\Framework\Fieldset;

/**
 * JSON Format
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class Table extends AbstractFormatter implements FormatterInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'table';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Table';

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
  ): ?string {
    $head = [];
    //if options
    if (isset($this->options)
      && is_array($this->options)
    ) {
      //set head
      $head = $this->options;
    }

    if (!is_array($value)) {
      $value = [];
    }

    //if numerical head
    if (!empty($head) && array_keys($head) === range(0, count($head) - 1)) {
      return $this->formatNumericalTable($head, $value);
    }

    //if there is parameters
    if (count($this->parameters) === 3) {
      if ($this->parameters[1] === 'hash') {
        return $this->formatSingleFieldsetTable($head, $value);
      }

      return $this->formatMultipleFieldsetTable($head, $value);
    }

    return $this->formatHeadlessTable($value);
  }

  /**
   * Renders the output format for object forms
   *
   * @param *array $rows
   *
   * @return string
   */
  public function formatHeadlessTable(array $rows): string
  {
    //if not numerical rows
    if (array_keys($rows) !== range(0, count($rows) - 1)) {
      //it's an associative array
      //so make it into a numerical rows
      $rows = [ $rows ];
    }

    $template = incept('handlebars')->compile(
      file_get_contents(__DIR__ . '/template/table/headless.html')
    );

    return $template([ 'rows' => $rows ]);
  }

  /**
   * Renders the output format for object forms
   *
   * @param *array $head
   * @param *array $rows
   *
   * @return string
   */
  public function formatNumericalTable(array $head, array $rows): string
  {
    //if not numerical rows
    if (array_keys($rows) !== range(0, count($rows) - 1)) {
      //it's an associative array
      //so make it into a numerical rows
      $rows = [ $rows ];
    }

    $filtered = [];
    foreach ($rows as $i => $row) {
      //should be the same count
      if (count($head) === count($row)) {
        $filtered[] = array_values($row);
      }
    }

    $template = incept('handlebars')->compile(
      file_get_contents(__DIR__ . '/template/table/numerical.html')
    );

    return $template([ 'head' => $head, 'rows' => $filtered ]);
  }

  /**
   * Renders the output format for object forms
   *
   * @param *array $head
   * @param *array $rows
   *
   * @return string
   */
  public function formatMultipleFieldsetTable(array $head, array $rows): string
  {
    //load the fieldset
    $fieldset = Fieldset::load($this->parameters[0]);

    $filtered = [];
    foreach ($rows as $i => $row) {
      $item = [];
      foreach ($head as $key => $label) {
        if (isset($row[$key])) {
          $item[$key] = $row[$key];
        }
      }

      $filtered[] = $item;
    }

    $template = incept('handlebars')->compile(
      file_get_contents(__DIR__ . '/template/table/fieldset/multiple.html')
    );

    return $template([
      'type' => $this->parameters[2],
      'head' => $head,
      'rows' => $filtered,
      'fieldset' => $fieldset->get()
    ]);
  }

  /**
   * Renders the output format for object forms
   *
   * @param *array $head
   * @param *array $row
   *
   * @return string
   */
  public function formatSingleFieldsetTable(array $head, array $row): string
  {
    //load the fieldset
    $fieldset = Fieldset::load($this->parameters[0]);

    $hash = [];
    foreach ($head as $key => $label) {
      if (isset($row[$key])) {
        $hash[] = [
          'key' => $key,
          'label' => $label,
          'row' => $row
        ];
      }
    }

    $template = incept('handlebars')->compile(
      file_get_contents(__DIR__ . '/template/table/fieldset/single.html')
    );

    return $template([
      'type' => $this->parameters[2],
      'hash' => $hash,
      'fieldset' => $fieldset->get()
    ]);
  }
}
