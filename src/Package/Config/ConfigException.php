<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Framework\Package\Config;

use Exception;

/**
 * Package exceptions
 *
 * @package  Incept
 * @category Package
 * @standard PSR-2
 */
class ConfigException extends Exception
{
  /**
   * @const ERROR_FILE_NOT_FOUND Error template
   */
  const ERROR_FILE_NOT_FOUND = 'File %s was not found';

  /**
   * @const ERROR_FOLDER_NOT_FOUND Error template
   */
  const ERROR_FOLDER_NOT_FOUND = 'Folder %s was not found';

  /**
   * @const ERROR_FOLDER_NOT_SET Error template
   */
  const ERROR_FOLDER_NOT_SET = 'Folder not set. Try $config->setFolder(string).';

  /**
   * Create a new exception for file not found
   *
   * @param *string $path
   *
   * @return ConfigException
   */
  public static function forFileNotFound(string $path): ConfigException
  {
    return new static(sprintf(static::ERROR_FILE_NOT_FOUND, $path));
  }

  /**
   * Create a new exception for folder not found
   *
   * @param *string $path
   *
   * @return ConfigException
   */
  public static function forFolderNotFound(string $path): ConfigException
  {
    return new static(sprintf(static::ERROR_FOLDER_NOT_FOUND, $path));
  }

  /**
   * Create a new exception for folder not set
   *
   * @return ConfigException
   */
  public static function forFolderNotSet(): ConfigException
  {
    return new static(sprintf(static::ERROR_FOLDER_NOT_SET));
  }
}
