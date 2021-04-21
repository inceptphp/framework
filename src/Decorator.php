<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace

{
  use Incept\Framework\Framework;

  if (!function_exists('incept')) {
    /**
     * The starting point of framework.
     *
     * Usage:
     * `incept()`
     * - returns the static (global) handler
     *
     * `incept(function() {})`
     * - returns the function
     * - used for scopes
     *
     * `incept('global')`
     * - returns the global package
     * - you can use any registered package
     *
     * `incept(Controller::class, 1, 2, 3)`
     * - Instantiates the given class
     * - with the following arguments
     *
     * @param mixed ...$args
     *
     * @return mixed
     */
    function incept(...$args)
    {
      static $framework = null;

      //if no framework set
      if (is_null($framework)) {
        //set a new framework
        $framework = new Framework;
      }

      //if no arguments
      if (func_num_args() == 0) {
        //return the static framework
        return $framework;
      }

      //if the first argument is callable
      if (is_callable($args[0])) {
        //call it
        $callback = array_shift($args);

        if ($callback instanceof Closure) {
          $callback = $callback->bindTo(
            $framework,
            get_class($framework)
          );
        }

        //and return the results
        return call_user_func_array($callback, $args);
      }

      //it could be a package
      if (count($args) === 1
        && is_string($args[0])
        && $framework->isPackage($args[0])
      ) {
        //yay, return it
        return $framework->package($args[0]);
      }

      //not sure what else would be useful
      //so lets just resolve things...
      return $framework->package('resolver')->resolve(...$args);
    }
  }
}

namespace Incept\Framework

{
  /**
   * When you do add in your file:
   * `Incept\Framework\Decorator::DECORATE;`
   *
   * It will enable the `incept()` to be called
   * I know its hax0r...
   *
   * @package  Incept
   * @category Framework
   * @standard PSR-2
   */
  class Decorator
  {
    /**
     * @const int DECORATE
     */
    const DECORATE = 1;
  }
}
