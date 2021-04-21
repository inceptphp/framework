<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Incept\Framework\Package\Host\HostPackage;

//map the package with the event package class methods
$this('host')
  //map the package with the event package class methods
  ->mapPackageMethods($this('resolver')->resolve(HostPackage::class, $this));
