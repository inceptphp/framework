<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Framework\Package\Config;

use UGComponents\Data\Registry;

/**
 * Config Package
 *
 * @vendor   Incept
 * @package  Package
 * @standard PSR-2
 */
class ConfigPackage
{
  /**
   * @const string PHP_HEADER
   */
  const PHP_HEADER = "<?php //-->\nreturn %s;";

  /**
   * @var string $path
   */
  protected $path;

  /**
   * @var Registry $registry
   */
  protected $registry;

  /**
   * Set the registry
   */
  public function __construct()
  {
    $this->registry = Registry::i();
  }

  /**
   * Returns true if the file+path exists
   *
   * @param *string  $file
   * @param string[] $path
   *
   * @return bool
   */
  public function exists(string $file, ...$path): bool
  {
    if (!is_dir((string) $this->path)) {
      throw ConfigException::forFolderNotSet();
    }

    $source = sprintf('%s/%s.php', $this->path, $file);

    //make sure we have the data from the file
    if (!$this->registry->exists($file) && file_exists($source)) {
      $this->registry->set($file, include $source);
    }

    if (empty($path)) {
      return $this->registry->exists($file);
    }

    //return the registry
    return $this->registry->exists($file, ...$path);
  }

  /**
   * Sets the origin where all configs are located
   *
   * @param *string $pathh whether to load the RnRs
   *
   * @return mixed
   */
  public function get(string $file, ...$path)
  {
    if (!is_dir((string) $this->path)) {
      throw ConfigException::forFolderNotSet();
    }

    $source = sprintf('%s/%s.php', $this->path, $file);

    // @codeCoverageIgnoreStart
    //make sure we have the data from the file
    if (!$this->exists($file) && file_exists($source)) {
      $this->registry->set($file, include $source);
    }
    // @codeCoverageIgnoreEnd

    if (empty($path)) {
      return $this->registry->get($file);
    }

    //return the registry
    return $this->registry->get($file, ...$path);
  }

  /**
   * Returns the origin where all configs are located
   *
   * @return ?string
   */
  public function getFolder(string $extra = null): ?string
  {
    if ($extra) {
      if (strpos($extra, '/') !== 0) {
        $extra = "/$extra";
      }

      return $this->path . $extra;
    }

    return $this->path;
  }

  /**
   * Removes everything from the registry
   *
   * @return ConfigPackage
   */
  public function purge()
  {
    $this->registry->set([]);
    return $this;
  }

  /**
   * Sets the origin where all configs are located
   *
   * @param *string $pathh whether to load the RnRs
   *
   * @return ConfigPackage
   */
  public function set(string $file, ...$path): ConfigPackage
  {
    if (!is_dir((string) $this->path)) {
      throw ConfigException::forFolderNotSet();
    }

    if (empty($path)) {
      return $this;
    }

    $destination = sprintf('%s/%s.php', $this->path, $file);

    // @codeCoverageIgnoreStart
    //make sure we have the data from the file
    if (!$this->exists($file) && file_exists($destination)) {
      $this->registry->set($file, include $destination);
    }
    // @codeCoverageIgnoreEnd

    //set the registry
    $this->registry->set($file, ...$path);

    // @codeCoverageIgnoreStart
    if (!is_dir(dirname($destination))) {
      mkdir(dirname($destination), 0777, true);
    }
    // @codeCoverageIgnoreEnd

    //save the file
    file_put_contents($destination, sprintf(
      static::PHP_HEADER,
      var_export($this->registry->get($file), true)
    ));

    return $this;
  }

  /**
   * Sets the origin where all configs are located
   *
   * @param *string $path whether to load the RnRs
   *
   * @return ConfigPackage
   */
  public function setFolder(string $path): ConfigPackage
  {
    if (!is_dir($path)) {
      throw ConfigException::forFolderNotFound($path);
    }

    $this->path = $path;
    return $this;
  }
}
