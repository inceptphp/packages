<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/package.php';

use Incept\Framework\Format\FormatterRegistry;
use Incept\Package\Format\FormatPackage;

$this
  //then load the package
  ->package('inceptphp/packages/format')
  //map the package with the event package class methods
  ->mapPackageMethods($this('resolver')->resolve(FormatPackage::class, $this));

//register formats

FormatterRegistry::register(Incept\Package\Format\String\Lowercase::class);

FormatterRegistry::register(Incept\Package\Format\String\Uppercase::class);

FormatterRegistry::register(Incept\Package\Format\String\Capitalize::class);

FormatterRegistry::register(Incept\Package\Format\String\CharLength::class);

FormatterRegistry::register(Incept\Package\Format\String\WordLength::class);

FormatterRegistry::register(Incept\Package\Format\Number\Number::class);

FormatterRegistry::register(Incept\Package\Format\Number\Price::class);

FormatterRegistry::register(Incept\Package\Format\Number\YesNo::class);

FormatterRegistry::register(Incept\Package\Format\Number\Rating::class);

FormatterRegistry::register(Incept\Package\Format\Date\Date::class);

FormatterRegistry::register(Incept\Package\Format\Date\Relative::class);

FormatterRegistry::register(Incept\Package\Format\Date\RelativeShort::class);

FormatterRegistry::register(Incept\Package\Format\Html\EscapeHtml::class);

FormatterRegistry::register(Incept\Package\Format\Html\StripHtml::class);

FormatterRegistry::register(Incept\Package\Format\Html\Markdown::class);

FormatterRegistry::register(Incept\Package\Format\Html\Link::class);

FormatterRegistry::register(Incept\Package\Format\Html\Image::class);

FormatterRegistry::register(Incept\Package\Format\Html\Email::class);

FormatterRegistry::register(Incept\Package\Format\Html\Phone::class);

FormatterRegistry::register(Incept\Package\Format\Json\SpaceSeparated::class);

FormatterRegistry::register(Incept\Package\Format\Json\CommaSeparated::class);

FormatterRegistry::register(Incept\Package\Format\Json\LineSeparated::class);

FormatterRegistry::register(Incept\Package\Format\Json\OrderedList::class);

FormatterRegistry::register(Incept\Package\Format\Json\UnorderedList::class);

FormatterRegistry::register(Incept\Package\Format\Json\TagList::class);

FormatterRegistry::register(Incept\Package\Format\Json\Meta::class);

FormatterRegistry::register(Incept\Package\Format\Json\Table::class);

FormatterRegistry::register(Incept\Package\Format\Json\Carousel::class);

FormatterRegistry::register(Incept\Package\Format\Json\Json::class);

FormatterRegistry::register(Incept\Package\Format\Custom\Custom::class);

FormatterRegistry::register(Incept\Package\Format\Custom\Formula::class);
