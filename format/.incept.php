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

FormatterRegistry::register(Incept\Package\Formatter\String\Lowercase::class);

FormatterRegistry::register(Incept\Package\Formatter\String\Uppercase::class);

FormatterRegistry::register(Incept\Package\Formatter\String\Capitalize::class);

FormatterRegistry::register(Incept\Package\Formatter\String\CharLength::class);

FormatterRegistry::register(Incept\Package\Formatter\String\WordLength::class);

FormatterRegistry::register(Incept\Package\Formatter\Number\Number::class);

FormatterRegistry::register(Incept\Package\Formatter\Number\Price::class);

FormatterRegistry::register(Incept\Package\Formatter\Number\YesNo::class);

FormatterRegistry::register(Incept\Package\Formatter\Number\Rating::class);

FormatterRegistry::register(Incept\Package\Formatter\Date\Date::class);

FormatterRegistry::register(Incept\Package\Formatter\Date\Relative::class);

FormatterRegistry::register(Incept\Package\Formatter\Date\RelativeShort::class);

FormatterRegistry::register(Incept\Package\Formatter\Html\EscapeHtml::class);

FormatterRegistry::register(Incept\Package\Formatter\Html\StripHtml::class);

FormatterRegistry::register(Incept\Package\Formatter\Html\Markdown::class);

FormatterRegistry::register(Incept\Package\Formatter\Html\Link::class);

FormatterRegistry::register(Incept\Package\Formatter\Html\Image::class);

FormatterRegistry::register(Incept\Package\Formatter\Html\Email::class);

FormatterRegistry::register(Incept\Package\Formatter\Html\Phone::class);

FormatterRegistry::register(Incept\Package\Formatter\Json\SpaceSeparated::class);

FormatterRegistry::register(Incept\Package\Formatter\Json\CommaSeparated::class);

FormatterRegistry::register(Incept\Package\Formatter\Json\LineSeparated::class);

FormatterRegistry::register(Incept\Package\Formatter\Json\OrderedList::class);

FormatterRegistry::register(Incept\Package\Formatter\Json\UnorderedList::class);

FormatterRegistry::register(Incept\Package\Formatter\Json\TagList::class);

FormatterRegistry::register(Incept\Package\Formatter\Json\Meta::class);

FormatterRegistry::register(Incept\Package\Formatter\Json\Table::class);

FormatterRegistry::register(Incept\Package\Formatter\Json\Carousel::class);

FormatterRegistry::register(Incept\Package\Formatter\Json\Json::class);

FormatterRegistry::register(Incept\Package\Formatter\Custom\Custom::class);

FormatterRegistry::register(Incept\Package\Formatter\Custom\Formula::class);
