<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Framework\Package\PDO;

use PDO;

use UGComponents\Package\Package;
use Incept\Framework\FrameworkHandler;

/**
 * PDO Package
 *
 * @vendor   Incept
 * @package  Package
 * @standard PSR-2
 */
class PDOPackage
{
  /**
   * @var *PackageHandler $handler
   */
  protected $handler;

  /**
   * @var *array $connections
   */
  protected array $connections = [];

  /**
   * @var *array $registry
   */
  protected array $registry = [];

  /**
   * Add handler for scope when routing
   *
   * @param *PackageHandler $handler
   */
  public function __construct(FrameworkHandler $handler)
  {
    $this->handler = $handler;
  }

  /**
   * Disconnect a PDO resource
   *
   * @param *string $name
   *
   * @return PDOPackage
   */
  public function disconnect(string $name): PDOPackage
  {
    if (isset($this->connections[$name])) {
      $this->connections[$name] = null;
      unset($this->connections[$name]);
    }

    return $this;
  }

  /**
   * returns a PDO resource
   *
   * @param *string $name
   * @param bool    $makeResource
   *
   * @return mixed
   */
  public function get(string $name, bool $makeResource = true)
  {
    //if it's not in the registry
    if (!isset($this->registry[$name])) {
      return null;
    }

    if (!$makeResource) {
      return $this->registry[$name];
    }

    //if no connection was made
    if (!isset($this->connections[$name])) {
      //assume the registry is a PDO resource
      $resource = $this->registry[$name];
      //if the resource is an array however,
      if (is_array($resource)) {
        //make it into a resource
        $resource = $this->makeResource($resource);
      }

      //set the connnection
      $this->connections[$name] = $resource;
    }

    //return the connection
    return $this->connections[$name];
  }

  /**
   * Returns true if the named resource is connected to the DB
   *
   * @param *string $name
   *
   * @return bool
   */
  public function connected(string $name)
  {
    //if it's not in the registry
    if (!isset($this->registry[$name])) {
      //it's not connected
      return false;
    }

    //return true if there is a connection
    return isset($this->connections[$name])
      //or the registry name is a PDO resource
      || $this->registry[$name] instanceof PDO;
  }

  /**
   * Registers a PDO
   *
   * @param *string    $name
   * @param *PDO|array $resource
   *
   * @return PDOPackage
   */
  public function register(string $name, array|PDO $resource): PDOPackage
  {
    //if it's registered
    if (isset($this->registry[$name])) {
      //release the resource first
      $this->disconnect($name);
    }

    $this->registry[$name] = $resource;
    return $this;
  }

  /**
   * Makes a PDO resource from a config array
   *
   * @param *array $resource
   *
   * @return PDO
   */
  protected function makeResource(array $config)
  {
    $host = $port = $name = $user = $pass = '';
    //if host
    if (isset($config['host']) && $config['host']) {
      //set host string
      $host = sprintf('host=%s;', $config['host']);
    }

    //if port
    if (isset($config['port']) && $config['port']) {
      //set port string
      $port = sprintf('port=%s;', $config['port']);
    }

    //if bane
    if (isset($config['name']) && $config['name']) {
      //set dbname string
      $name = sprintf('dbname=%s;', $config['name']);
    }

    //if user
    if (isset($config['user']) && $config['user']) {
      //set user string
      $user = $config['user'];
    }

    // @codeCoverageIgnoreStart
    //if pass
    if (isset($config['pass']) && $config['pass']) {
      //set pass string
      $pass = $config['pass'];
    }
    // @codeCoverageIgnoreEnd

    $options = [];
    // @codeCoverageIgnoreStart
    //if options
    if (isset($config['options']) && is_array($config['options'])) {
      //set options
      $options = $config['options'];
    }
    // @codeCoverageIgnoreEnd

    $type = 'mysql';
    if (isset($config['type']) && $config['type']) {
      $type = $config['type'];
    }

    //make a connection string
    $connection = sprintf('%s:%s%s%s', $type, $host, $port, $name);

    //get the resolver
    $resolver = $this->handler->package('resolver');

    //load the pdo
    return $resolver->resolve(
      PDO::class,
      $connection,
      $user,
      $pass,
      $options
    );
  }

  /**
   * Removes all PDO connections
   *
   * @return PDOPackage
   */
  public function purgeRegistry(): PDOPackage
  {
    //empty one by one, releasing the PDO resource pointer
    foreach(array_keys($this->registry) as $name) {
      $this->unregister($name);
    }
    $this->registry = [];
    return $this;
  }

  /**
   * Removes a PDO from the registry
   *
   * @param *string $name
   *
   * @return PDOPackage
   */
  public function unregister(string $name): PDOPackage
  {
    //if there's a connection
    if (isset($this->connections[$name])) {
      //disconnect
      $this->disconnect($name);
    }

    if (isset($this->registry[$name])) {
      $this->registry[$name] = null;
      unset($this->registry[$name]);
    }

    return $this;
  }
}
