<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Incept\Framework\Package\Resolver\ResolverPackage;

//map the package with the resolver package class methods
$this('resolver')->mapPackageMethods(new ResolverPackage);
