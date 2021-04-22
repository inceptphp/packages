<?php //-->

use Incept\Framework\Schema;

$this('handlebars')
  /**
   * Makes a default filter field
   *
   * @param *string      string to translate
   * @param string[..string] sprintf variables
   *
   * @return string
   */
  ->registerHelper('admin_filter', function ($schema, $filters, $options) {
    if (!is_array($schema)) {
      $schema = Schema::load($schema);
    } else {
      $schema = Schema::i($schema);
    }

    $output = [];

    $fields = $schema->getFields();
    foreach ($schema->getFields('filterable') as $key => $field) {
      $field = $schema->makeField($key);

      if (!$field) {
        continue;
      }

      $path = $value = null;
      if (isset($filters['filter'][$key])) {
        $path = sprintf('filter.%s', $key);
        $value = $filters['filter'][$key];
      } else if (isset($filters['in'][$key])) {
        $path = sprintf('in.%s', $key);
        $value = $filters['in'][$key];
      } else if (isset($filters['span'][$key])) {
        $path = sprintf('span.%s', $key);
        $value = $filters['span'][$key];
      }

      $control = $field->renderFilter($value, $key);

      if (!$control) {
        continue;
      }

      $output[] = $options['fn']([
        'path' => $path,
        'type' => $field::NAME,
        'label' => $fields[$key]['label'],
        'value' => $value,
        'control' => $control,
      ]);
    }

    return implode("\n", $output);
  })

  /**
   * Makes a default filter field
   *
   * @param *string      string to translate
   * @param string[..string] sprintf variables
   *
   * @return string
   */
  ->registerHelper('admin_thead', function ($schema, $options) {
    if (!is_array($schema)) {
      $schema = Schema::load($schema);
    } else {
      $schema = Schema::i($schema);
    }

    $output = [];

    //first is the ID
    $output[] = $options['fn']([
      'is_id' => true,
      'sortable' => 1,
      'name' => $schema->getPrimaryName(),
      'label' => 'ID'
    ]);

    //next is the 1:1 relations
    foreach($schema->getRelations(1) as $relation) {
      $output[] = $options['fn']([ 'label' => $relation['singular'] ]);
    }

    //next is the columns
    foreach($schema->getFields('listed') as $field) {
      $output[] = $options['fn']($field);
    }

    return implode("\n", $output);
  })

  /**
   * Makes a default filter field
   *
   * @param *string      string to translate
   * @param string[..string] sprintf variables
   *
   * @return string
   */
  ->registerHelper('admin_tbody', function ($schema, $rows, $options) {
    if (!is_array($schema)) {
      $schema = Schema::load($schema);
    } else {
      $schema = Schema::i($schema);
    }

    $primary = $schema->getPrimaryName();
    //get 1:1 relation schemas only
    $relations = $schema->getRelations(1);
    //get listed columns only
    $columns = $schema->getFields('listed');

    $output = [];

    foreach ($rows as $i => $row) {
      $row['@index'] = $i;
      $row['this'] = $row;
      $row['item'] = $row;
      $row['suggestion'] = $schema->getSuggestion($row);

      $row['schema'] = $schema->get();
      $row['schema']['columns'] = $columns;
      $row['schema']['primary'] = $primary;
      $row['schema']['id'] = $row[$primary];

      foreach ($relations as $table => $relation) {
        $name = $relation->getName();
        $primary = $relation->getPrimaryName();

        $row['relations'][$table] = $relation->get();
        //pass the following for scope convenience
        $row['relations'][$table]['primary'] = $primary;
        $row['relations'][$table]['schema'] = $row['schema'];

        if (isset($row['item'][$name])) {
          $row['relations'][$table]['suggestion'] = $relation->getSuggestion(
            $row['item'][$name]
          );

          $row['relations'][$table]['id'] = $data['item'][$name][$primary];
          $row['relations'][$table]['item'] = $row['item'][$name];
        }
      }

      $output[] = $options['fn']($row);
    }

    return implode("\n", $output);
  })

;
