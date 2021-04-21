<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Incept\Framework\Package\PDO\PDOPackage;

//map the package with the event package class methods
$this('pdo')->mapPackageMethods($this('resolver')->resolve(PDOPackage::class, $this));
