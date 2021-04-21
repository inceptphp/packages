<?php //-->

use Incept\Framework\Schema;
use Incept\Framework\Field\FieldRegistry;

/**
 * Makes a default field
 *
 * @param *string $name
 * @param ?array  $row
 * @param ?array  $options
 *
 * @return string
 */
$this('handlebars')->registerHelper('field', function (array $options) {
  //get attributes from hash
  $attributes = $options['hash'];

  //determine row
  $row = [];
  if (isset($attributes['row'])) {
    $row = $attributes['row'];
    unset($attributes['row']);
  }

  //determine name
  $name = null;
  if (isset($attributes['name'])) {
    $name = $attributes['name'];
    unset($attributes['name']);
  }
  //eg. fieldname - profile_address[address_street_1]
  //eg. name - address_street_1
  $fieldName = $name;
  if (strpos($name, '[') !== false
    && preg_match_all('/\[([^\]]+)\]/', $name, $matches)
    && isset($matches[1])
    && count($matches[1])
  ) {
    $name = array_pop($matches[1]);
  }

  //determine value
  $value = null;
  if (isset($row[$name])) {
    $value = $row[$name];
  } else if (isset($attributes['value'])) {
    $value = $attributes['value'];
    unset($attributes['value']);
  }

  //if there is a schema
  //then the attributes, options and parameters are there
  if (isset($attributes['schema'])) {
    //load the schema
    if (!is_array($attributes['schema'])) {
      $schema = Schema::load($attributes['schema']);
    } else {
      $schema = Schema::i($attributes['schema']);
    }

    $field = $schema->makeField($name, $fieldName);

    //if no field
    if (!$field) {
      return $options['inverse']();
    }

    //render the field
    $control = $field->render($value, $name, $row);

    //if not redered
    if (!$control) {
      return $options['inverse']();
    }

    //get the label
    $fields = $schema->getFields();
    $label = $fields[$name]['label'];

    $output = $options['fn']([
      'type' => $field::NAME,
      'label' => $label,
      'control' => $control
    ]);

    //if a block was defined
    if ($output) {
      //return the block
      return $output;
    }

    //return the control field
    return $control;
  }

  //if we are here then it means no schema was given
  //so we have to extract the options and parameters
  //from the attributes

  //determine options
  $option = [];
  if (isset($attributes['options'])) {
    $option = $attributes['options'];
    unset($attributes['options']);
  }

  //determine parameters
  $parameters = [];
  if (isset($attributes['parameters'])) {
    $parameters = $attributes['parameters'];
    unset($attributes['parameters']);
  } else {
    for ($i = 1; $i < 1000; $i++) {
      if (!isset($attributes['parameters-' . $i])) {
        break;
      }

      $parameters[] = $attributes['parameters-' . $i];
      unset($attributes['parameters-' . $i]);
    }
  }

  //determine type
  $type = 'input';
  if (isset($attributes['type'])) {
    $type = $attributes['type'];
    unset($attributes['type']);
  }

  //determine label
  $label = null;
  if (isset($attributes['label'])) {
    $label = $attributes['label'];
    unset($attributes['label']);
  }

  //load up the field
  $field = FieldRegistry::makeField($type);

  //if no field
  if (!$field) {
    return $options['inverse']();
  }

  //set name
  $control = $field
    ->setName($fieldName)
    ->setAttributes($attributes)
    ->setOptions($option)
    ->setParameters($parameters)
    ->render($value, $name, $row);

  $output = $options['fn']([
    'type' => $field::NAME,
    'label' => $label,
    'control' => $control
  ]);

  if ($output) {
    return $output;
  }

  return $control;
});
