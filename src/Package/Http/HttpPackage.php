<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Framework\Package\Http;

use Closure;

use UGComponents\Http\HttpTrait;

use UGComponents\Event\EventTrait;

use UGComponents\Helper\LoopTrait;
use UGComponents\Helper\ConditionalTrait;

use UGComponents\Profiler\InspectorTrait;
use UGComponents\Profiler\LoggerTrait;

use UGComponents\Resolver\StateTrait;

use Incept\Framework\Framework;

/**
 * Http Package
 *
 * @vendor   Incept
 * @package  Package
 * @standard PSR-2
 */
class HttpPackage
{
  use HttpTrait,
    EventTrait,
    LoopTrait,
    ConditionalTrait,
    InspectorTrait,
    LoggerTrait,
    StateTrait
    {
      HttpTrait::route as routeHttp;
      HttpTrait::all as allHttp;
      HttpTrait::delete as deleteHttp;
      HttpTrait::get as getHttp;
      HttpTrait::post as postHttp;
      HttpTrait::put as putHttp;
  }

  /**
   * @const string ALL
   */
  const ALL = 'all';

  /**
   * @const string GET
   */
  const GET = 'get';

  /**
   * @const string POST
   */
  const POST = 'post';

  /**
   * @const string PUT
   */
  const PUT = 'put';

  /**
   * @const string DELETE
   */
  const DELETE = 'delete';

  /**
   * @const string OPTIONS
   */
  const OPTIONS = 'options';

  /**
   * @var *PackageHandler $handler
   */
  protected $handler;

  /**
   * Add handler for scope when routing
   *
   * @param *PackageHandler $handler
   */
  public function __construct(Framework $handler)
  {
    $this->handler = $handler;
  }

  /**
   * Adds routing middleware for all methods
   *
   * @param *string      $path   The route path
   * @param *callable|string $callback The middleware handler
   * @param callable|string  ...$args  Arguments for flow
   *
   * @return HttpPackage
   */
  public function all(string $path, $callback, ...$args): HttpPackage
  {
    return $this->route(static::ALL, $path, $callback, ...$args);
  }

  /**
   * Adds routing middleware for DELETE method
   *
   * @param *string      $path   The route path
   * @param *callable|string $callback The middleware handler
   * @param callable|string  ...$args  Arguments for flow
   *
   * @return HttpPackage
   */
  public function delete(string $path, $callback, ...$args): HttpPackage
  {
    return $this->route(static::DELETE, $path, $callback, ...$args);
  }

  /**
   * Adds routing middleware for GET method
   *
   * @param *string      $path   The route path
   * @param *callable|string $callback The middleware handler
   * @param callable|string  ...$args  Arguments for flow
   *
   * @return HttpPackage
   */
  public function get(string $path, $callback, ...$args): HttpPackage
  {
    return $this->route(static::GET, $path, $callback, ...$args);
  }

  /**
   * Adds routing middleware for OPTIONS method
   *
   * @param *string      $path   The route path
   * @param *callable|string $callback The middleware handler
   * @param callable|string  ...$args  Arguments for flow
   *
   * @return HttpPackage
   */
  public function options(string $path, $callback, ...$args): HttpPackage
  {
    return $this->route(static::OPTIONS, $path, $callback, ...$args);
  }

  /**
   * Adds routing middleware for POST method
   *
   * @param *string      $path   The route path
   * @param *callable|string $callback The middleware handler
   * @param callable|string  ...$args  Arguments for flow
   *
   * @return HttpPackage
   */
  public function post(string $path, $callback, ...$args): HttpPackage
  {
    return $this->route(static::POST, $path, $callback, ...$args);
  }

  /**
   * Adds routing middleware for PUT method
   *
   * @param *string      $path   The route path
   * @param *callable|string $callback The middleware handler
   * @param callable|string  ...$args  Arguments for flow
   *
   * @return HttpPackage
   */
  public function put(string $path, $callback, ...$args): HttpPackage
  {
    return $this->route(static::PUT, $path, $callback, ...$args);
  }

  /**
   * Short Hand Redirect
   *
   * @param *string $path
   * @param bool    $force
   *
   * @return HttpPackage
   */
  public function redirect(string $path, bool $force = false): HttpPackage
  {
    // @codeCoverageIgnoreStart
    if ($force) {
      header('Location: ' . $path);
      exit;
    }
    // @codeCoverageIgnoreEnd

    $this->handler
      ->package('http')
      ->getResponse()
      ->addHeader('Location', $path);

    return $this;
  }

  /**
   * Adds routing middleware
   *
   * @param *string      $method   The request method
   * @param *string      $path   The route path
   * @param *callable|string $callback The middleware handler
   * @param callable|string  ...$args  Arguments for flow
   *
   * @return HttpPackage
   */
  public function route(
    string $method,
    string $path,
    $callback,
    ...$args
  ): HttpPackage {
    $router = $this->getRouter();
    array_unshift($args, $callback);

    foreach ($args as $i => $callback) {
      $priority = 0;
      if (isset($args[$i + 1]) && is_numeric($args[$i + 1])) {
        $priority = $args[$i + 1];
      }

      //if it's a string
      if (is_string($callback)) {
        //it's an event
        $flow = $callback;
        //make into callback
        $callback = function (...$args) use ($flow) {
          $this('event')->emit($flow, ...$args);
        };
      }

      //if it's closure
      if ($callback instanceof Closure) {
        //bind it
        $callback = $this->handler->bindCallback($callback);
      }

      //if it's callable
      if (is_callable($callback)) {
        //route it
        $router->route($method, $path, $callback, $priority);
      }
    }

    return $this;
  }
}
