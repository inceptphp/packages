<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Field\File;

use Incept\Framework\Field\AbstractField;
use Incept\Framework\Field\FieldInterface;
use Incept\Framework\Field\FieldTypes;
use Incept\Framework\Format\FormatTypes;

/**
 * File List Field
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class FileList extends AbstractField implements FieldInterface
{
  /**
   * @const bool HAS_ATTRIBUTES Whether or not to show attribute fieldset
   * on the schema form if the field was chosen
   */
  const HAS_ATTRIBUTES = true;

  /**
   * @const string NAME Config name
   */
  const NAME = 'filelist';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'File List Fieldset';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = FieldTypes::TYPE_FILE;

  /**
   * @const array TYPES List of possible data types
   */
  const TYPES = [
    FieldTypes::TYPE_STRING_LIST,
    FieldTypes::TYPE_JSON,
    FieldTypes::TYPE_OBJECT
  ];

  /**
   * @const array FORMATS List of possible formats
   */
  const FORMATS = [
    FormatTypes::TYPE_JSON
  ];

  /**
   * Prepares the value for some sort of insertion
   *
   * @param ?mixed  $value
   * @param ?string $name  name of the column in the row
   * @param ?array  $row   the row submitted with the value
   *
   * @return ?scalar
   */
  public function prepare($value = null, string $name = null, array $row = [])
  {
    if (is_null($value)) {
      return $value;
    }

    $files = incept('event')->call('file-upload', [ 'data' => $value ]);

    if (!isset($files['data'])) {
      return json_encode($value);
    }

    return json_encode($file['data']);
  }

  /**
   * Renders the field for object forms
   *
   * @param ?mixed  $value
   * @param ?string $name  name of the column in the row
   * @param ?array  $row   the row submitted with the value
   *
   * @return ?string
   */
  public function render(
    $value = null,
    string $name = null,
    array $row = []
  ): ?string {
    $template = incept('handlebars')->compile(
      file_get_contents(__DIR__ . '/template/field/filelist.html')
    );
    return $template([
      'name' => $this->name,
      'value' => $value,
      'attributes' => $this->attributes,
      'options' => $this->options,
      'parameters' => $this->parameters
    ]);
  }
}
