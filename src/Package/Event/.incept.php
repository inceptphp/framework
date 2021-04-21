<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Incept\Framework\Package\Event\EventPackage;

$this('event')
  //map the package with the event package class methods
  ->mapPackageMethods($this('resolver')->resolve(EventPackage::class, $this))
  //use one global resolver
  ->setResolverHandler($this('resolver')->getResolverHandler());
