<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use UGComponents\I18n\Timezone;

//map the package with the event package class methods
$this('tz')->mapPackageMethods($this('resolver')->resolve(Timezone::class, 'GMT'));
