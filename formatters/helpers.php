<?php //-->

use Incept\Framework\Schema;

/**
 * Makes a default field
 *
 * @param *string      string to translate
 * @param string[..string] sprintf variables
 *
 * @return string
 */
$this('handlebars')->registerHelper('format', function ($schema, $name, $row, $type, $options) {
  if (!is_array($schema)) {
    $schema = Schema::load($schema);
  } else {
    $schema = Schema::i($schema);
  }

  $value = null;
  if (isset($row[$name])) {
    $value = $row[$name];
  }

  $fields = $schema->getFields();
  $formatter = $schema->makeFormatter($name, $type);

  $control = null;
  if ($formatter) {
    $control = $formatter->format($value, $name, $row);
  }

  if ($control) {
    $output = $options['fn']([
      'label' => $fields[$name]['label'],
      'control' => $control
    ]);

    if ($output) {
      return $output;
    }

    return $control;
  }

  return $options['inverse']();
});
