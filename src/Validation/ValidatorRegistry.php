<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Framework\Validation;

/**
 * Stores validators that are registered as usable
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class ValidatorRegistry
{
  /**
   * @var array $validators
   */
  protected static $validators = [];

  /**
   * Returns a validator
   *
   * @param *string $name
   *
   * @return ?string
   */
  public static function getValidator(string $name): ?string
  {
    if (isset(self::$validators[$name])) {
      return self::$validators[$name];
    }

    return null;
  }

  /**
   * Returns all validators
   *
   * @return array
   */
  public static function getValidators(): array
  {
    return self::$validators;
  }

  /**
   * Returns a validator instance
   *
   * @param *string $name
   *
   * @return ?ValidatorInterface
   */
  public static function makeValidator(string $name): ?ValidatorInterface
  {
    if (isset(self::$validators[$name])) {
      return incept('resolver')->resolve(self::$validators[$name]);
    }

    return null;
  }

  /**
   * Registers a validator
   *
   * @param *string $validator
   *
   * @return bool true if was registered successfully
   */
  public static function register(string $validator)
  {
    if (!is_subclass_of($validator, ValidatorInterface::class)
      || !$validator::NAME
    ) {
      return false;
    }

    self::$validators[$validator::NAME] = $validator;
    return true;
  }
}
