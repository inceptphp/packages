<?php //-->

return function($field, $encode) {
  $sample = null;
  if ($field['field']['type'] === 'text') {
    if (strpos($name, 'reference') !== false) {
      $sample = '123-456';
    } else if (strpos($name, 'title') !== false) {
      $sample = 'Sample Title';
    } else if (strpos($name, 'currency') !== false) {
      $sample = 'php';
    } else {
      $sample = 'sample';
    }
  } else if ($field['field']['type'] === 'email') {
    $sample = 'jane@doe.com';
  } else if ($field['field']['type'] === 'password') {
    $sample = 'password-1234';
  } else if ($field['field']['type'] === 'search') {
    $sample = 'iphone';
  } else if ($field['field']['type'] === 'url') {
    $sample = 'https://iamawesome.com';
  } else if ($field['field']['type'] === 'color') {
    $sample = '#EF12FE';
  } else if ($field['field']['type'] === 'mask') {
    $sample = '123-123-12';
  } else if ($field['field']['type'] === 'slug') {
    $sample = 'iphone-xr';
  } else if ($field['field']['type'] === 'textarea') {
    $sample = 'Same Long Text';
  } else if ($field['field']['type'] === 'wysiwyg') {
    $sample = 'Same Long Text';
  } else if ($field['field']['type'] === 'markdown') {
    $sample = 'Same Long Text';
  } else if ($field['field']['type'] === 'code') {
    $sample = 'Same Long Text';
  } else if ($field['field']['type'] === 'number') {
    if (isset($field['field']['attributes']['step'])
      && $field['field']['attributes']['step'] > 0
      && $field['field']['attributes']['step'] < 1
    ) {
      $sample = '1.01';
    } else {
      $sample = '10';
    }
  } else if ($field['field']['type'] === 'small') {
    $sample = '1';
  } else if ($field['field']['type'] === 'range') {
    if (isset($field['field']['attributes']['step'])
      && $field['field']['attributes']['step'] > 0
      && $field['field']['attributes']['step'] < 1
    ) {
      $sample = '1.01';
    } else {
      $sample = '10';
    }
  } else if ($field['field']['type'] === 'float') {
    $sample = '10.01';
  } else if ($field['field']['type'] === 'price') {
    $sample = '10.01';
  } else if ($field['field']['type'] === 'stars') {
    $sample = '3';
  } else if ($field['field']['type'] === 'date') {
    $sample = date('Y-m-d');
  } else if ($field['field']['type'] === 'time') {
    $sample = date('H:i:s');
  } else if ($field['field']['type'] === 'datetime') {
    $sample = date('Y-m-d H:i:s');;
  } else if ($field['field']['type'] === 'week') {
    $sample = '25';
  } else if ($field['field']['type'] === 'month') {
    $sample = '4';
  } else if ($field['field']['type'] === 'checkbox') {
    $sample = '1';
  } else if ($field['field']['type'] === 'switch') {
    $sample = '1';
  } else if ($field['field']['type'] === 'knob') {
  } else if ($field['field']['type'] === 'select') {
    if (isset($field['field']['options'][0])) {
      $sample = $field['field']['options'][0];
    } else {
      $sample = 'foo';
    }
  } else if ($field['field']['type'] === 'multiselect') {
    if (isset($field['field']['options'][0])) {
      $sample = $field['field']['options'][0];
    } else {
      $sample = 'foo';
    }
  } else if ($field['field']['type'] === 'checkboxes') {
    if (isset($field['field']['options'][0])) {
      $sample = $field['field']['options'][0];
    } else {
      $sample = 'foo';
    }
  } else if ($field['field']['type'] === 'radios') {
    if (isset($field['field']['options'][0])) {
      $sample = $field['field']['options'][0];
    } else {
      $sample = 'foo';
    }
  } else if ($field['field']['type'] === 'countrylist') {
    $sample = 'US';
  } else if ($field['field']['type'] === 'file') {
    $sample = 'https://iamawesome.com/file.pdf';
  } else if ($field['field']['type'] === 'filelist') {
    $sample = 'https://iamawesome.com/file.pdf';
  } else if ($field['field']['type'] === 'image') {
    $sample = 'https://iamawesome.com/image.jpg';
  } else if ($field['field']['type'] === 'imagelist') {
    $sample = 'https://iamawesome.com/image.jpg';
  } else if ($field['field']['type'] === 'tag') {
    $sample = 'sale';
  } else if ($field['field']['type'] === 'textlist') {
    $sample = 'sample';
  } else if ($field['field']['type'] === 'textarealist') {
    $sample = 'Sample Log Text';
  } else if ($field['field']['type'] === 'wysiwyglist') {
    $sample = 'Sample Log Text';
  } else if ($field['field']['type'] === 'meta') {
    $sample = '1234';
  } else if ($field['field']['type'] === 'table') {
    $sample = '1234';
  } else if ($field['field']['type'] === 'fieldset') {
  } else if ($field['field']['type'] === 'multirange') {
    $sample = '10';
  } else if ($field['field']['type'] === 'latlng') {
  } else if ($field['field']['type'] === 'rawjson') {
  }

  if (isset($field['default']) && strlen($field['default'])) {
    $sample = $field['default'];
  }

  if ($encode) {
    $sample = urlencode($sample);
  }

  return $sample;
};
