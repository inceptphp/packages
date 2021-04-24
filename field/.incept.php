<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/package.php';

use Incept\Framework\Field\FieldRegistry;
use Incept\Package\Field\FieldPackage;

$this
  //then load the package
  ->package('inceptphp/packages/field')
  //map the package with the event package class methods
  ->mapPackageMethods($this('resolver')->resolve(FieldPackage::class, $this));

//register fields

FieldRegistry::register(Incept\Package\Field\Input\Input::class);

FieldRegistry::register(Incept\Package\Field\Input\Text::class);

FieldRegistry::register(Incept\Package\Field\Input\Color::class);

FieldRegistry::register(Incept\Package\Field\Input\Email::class);

FieldRegistry::register(Incept\Package\Field\Input\Phone::class);

FieldRegistry::register(Incept\Package\Field\Input\Url::class);

FieldRegistry::register(Incept\Package\Field\Input\Slug::class);

FieldRegistry::register(Incept\Package\Field\Input\Mask::class);

FieldRegistry::register(Incept\Package\Field\Input\Password::class);

FieldRegistry::register(Incept\Package\Field\Date\Date::class);

FieldRegistry::register(Incept\Package\Field\Date\Time::class);

FieldRegistry::register(Incept\Package\Field\Date\Datetime::class);

FieldRegistry::register(Incept\Package\Field\Date\Week::class);

FieldRegistry::register(Incept\Package\Field\Date\Month::class);

FieldRegistry::register(Incept\Package\Field\Number\Number::class);

FieldRegistry::register(Incept\Package\Field\Number\Floating::class);

FieldRegistry::register(Incept\Package\Field\Number\Price::class);

FieldRegistry::register(Incept\Package\Field\Number\Range::class);

FieldRegistry::register(Incept\Package\Field\Number\Rating::class);

FieldRegistry::register(Incept\Package\Field\Number\Small::class);

FieldRegistry::register(Incept\Package\Field\Number\Knob::class);

FieldRegistry::register(Incept\Package\Field\Textarea\Textarea::class);

FieldRegistry::register(Incept\Package\Field\Textarea\Wysiwyg::class);

FieldRegistry::register(Incept\Package\Field\Textarea\Markdown::class);

FieldRegistry::register(Incept\Package\Field\Textarea\Code::class);

FieldRegistry::register(Incept\Package\Field\Option\Select::class);

FieldRegistry::register(Incept\Package\Field\Option\Radio::class);

FieldRegistry::register(Incept\Package\Field\Option\Country::class);

FieldRegistry::register(Incept\Package\Field\Option\Currency::class);

FieldRegistry::register(Incept\Package\Field\Option\Checkbox::class);

FieldRegistry::register(Incept\Package\Field\Option\SwitchField::class);

FieldRegistry::register(Incept\Package\Field\Option\CheckList::class);

FieldRegistry::register(Incept\Package\Field\Option\MultiSelect::class);

FieldRegistry::register(Incept\Package\Field\File\File::class);

FieldRegistry::register(Incept\Package\Field\File\Image::class);

FieldRegistry::register(Incept\Package\Field\File\FileList::class);

FieldRegistry::register(Incept\Package\Field\File\ImageList::class);

FieldRegistry::register(Incept\Package\Field\Json\TagList::class);

FieldRegistry::register(Incept\Package\Field\Json\TextList::class);

FieldRegistry::register(Incept\Package\Field\Json\TextareaList::class);

FieldRegistry::register(Incept\Package\Field\Json\Meta::class);

FieldRegistry::register(Incept\Package\Field\Json\Table::class);

FieldRegistry::register(Incept\Package\Field\Json\MultiRange::class);

FieldRegistry::register(Incept\Package\Field\Json\LatLng::class);

FieldRegistry::register(Incept\Package\Field\Json\Table::class);

FieldRegistry::register(Incept\Package\Field\Json\Json::class);

FieldRegistry::register(Incept\Package\Field\Json\Fieldset::class);

FieldRegistry::register(Incept\Package\Field\Custom\Uuid::class);

FieldRegistry::register(Incept\Package\Field\Custom\IpAddress::class);
