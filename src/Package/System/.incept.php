<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Incept\Framework\Package\System\SystemPackage;

require_once __DIR__ . '/events/collection.php';
require_once __DIR__ . '/events/fieldset.php';
require_once __DIR__ . '/events/object.php';
require_once __DIR__ . '/events/relation.php';
require_once __DIR__ . '/events/schema.php';

use Incept\Framework\Field\FieldRegistry;
use Incept\Framework\Validation\ValidatorRegistry;
use Incept\Framework\Format\FormatterRegistry;

//map the package with the event package class methods
$this('system')
  //map the package with the event package class methods
  ->mapPackageMethods($this('resolver')->resolve(SystemPackage::class, $this));

//register fields
FieldRegistry::register(Incept\Framework\Field\None::class);

FieldRegistry::register(Incept\Framework\Field\Active::class);

FieldRegistry::register(Incept\Framework\Field\Created::class);

FieldRegistry::register(Incept\Framework\Field\Updated::class);

//register validators
ValidatorRegistry::register(Incept\Framework\Validation\Required::class);

ValidatorRegistry::register(Incept\Framework\Validation\Unique::class);

//register formats
FormatterRegistry::register(Incept\Framework\Format\None::class);

FormatterRegistry::register(Incept\Framework\Format\Hide::class);
