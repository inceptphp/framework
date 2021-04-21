<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Incept\Framework\Package\Config\ConfigPackage;

//map the package with the event package class methods
$this('config')->mapPackageMethods($this('resolver')->resolve(ConfigPackage::class));
