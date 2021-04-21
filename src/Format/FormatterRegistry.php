<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Framework\Format;

/**
 * Stores formatters that are registered as usable
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class FormatterRegistry
{
  /**
   * @var array $formatters
   */
  protected static $formatters = [];

  /**
   * Returns a format
   *
   * @param *string $name
   *
   * @return ?string
   */
  public static function getFormatter(string $name): ?string
  {
    if (isset(self::$formatters[$name])) {
      return self::$formatters[$name];
    }

    return null;
  }

  /**
   * Returns all formatters
   *
   * @return array
   */
  public static function getFormatters(): array
  {
    return self::$formatters;
  }

  /**
   * Returns a format instance
   *
   * @param *string $name
   *
   * @return ?FormatterInterface
   */
  public static function makeFormatter(string $name): ?FormatterInterface
  {
    if (isset(self::$formatters[$name])) {
      return incept()->resolve(self::$formatters[$name]);
    }

    return null;
  }

  /**
   * Registers a format
   *
   * @param *string $field
   *
   * @return bool true if was registered successfully
   */
  public static function register(string $formatter)
  {
    if (!is_subclass_of($formatter, FormatterInterface::class)
      || !$formatter::NAME
    ) {
      return false;
    }

    self::$formatters[$formatter::NAME] = $formatter;
    return true;
  }
}
