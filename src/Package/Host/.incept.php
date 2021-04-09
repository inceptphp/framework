<?php //-->

use Incept\Framework\Package\Host\HostPackage;

//map the package with the event package class methods
$this('host')
  //map the package with the event package class methods
  ->mapPackageMethods($this('resolver')->resolve(HostPackage::class, $this));
