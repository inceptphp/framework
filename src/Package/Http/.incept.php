<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Incept\Framework\Package\Http\HttpPackage;

$this('http')
  //map the package with the http package class methods
  ->mapPackageMethods($this('resolver')->resolve(HttpPackage::class, $this))
  //use one global resolver
  ->setResolverHandler($this('resolver')->getResolverHandler())
  //use one global request
  ->setRequest($this->getRequest())
  //use one global response
  ->setResponse($this->getResponse())
  //use one global event emitter
  ->setEventEmitter($this('event')->getEventEmitter());
