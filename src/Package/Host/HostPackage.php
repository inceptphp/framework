<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Framework\Package\Host;

use Incept\Framework\Framework;

/**
 * Host Package
 *
 * @vendor   Incept
 * @package  Package
 * @standard PSR-2
 */
class HostPackage
{
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
   * Returns all host values
   *
   * @return string
   */
  public function all(): array
  {
    return [
      'base' => $this->base(),
      'dir' => $this->dir(),
      'domain' => $this->domain(),
      'name' => $this->name(),
      'path' => $this->path(),
      'relative' => $this->relative(),
      'url' => $this->url(),
    ];
  }

  /**
   * Returns the hostbose
   *
   * ex. http://www.example.com/some/page.html (with no end slash)
   *
   * @return string
   */
  public function base(): string
  {
    //url and base
    $base = $this->url();
    if (strpos($base, '?') !== false) {
      $base = substr($base, 0, strpos($base, '?'));
    }

    return $base;
  }

  /**
   * Returns the urldir
   *
   * ex. /some (with no end slash)
   *
   * @return string
   */
  public function dir(): string
  {
    return pathinfo($this->path(), PATHINFO_DIRNAME);
  }

  /**
   * Returns the domain name
   *
   * ex. www.example.com
   *
   * @return string
   */
  public function domain(): string
  {
    return $_SERVER['HTTP_HOST'];
  }

  /**
   * Returns the hostname
   *
   * ex. http://www.example.com (with no end slash)
   *
   * @return string
   */
  public function name(): string
  {
    //protocol
    $protocol = 'http';
    if ($_SERVER['SERVER_PORT'] != 80) {
      $protocol = 'https';
    }

    if (($this->handler)('config')->get('settings', 'https')) {
      $protocol = 'https';
    }

    return sprintf('%s://%s', $protocol, $this->domain());
  }

  /**
   * Returns the urlpath
   *
   * ex. /some/page.html (with no end slash)
   *
   * @return string
   */
  public function path(): string
  {
    $path = $_SERVER['REQUEST_URI'];
    if (strpos($path, '?') !== false) {
      $path = substr($path, 0, strpos($path, '?'));
    }

    if (substr($path, -1) === '/') {
      $path = substr($path, 0, -1);
    }

    return $path;
  }

  /**
   * Returns the relative url
   *
   * ex. /some/page.html?foo=bar
   *
   * @return string
   */
  public function relative(): string
  {
    $query = null;
    $path = $this->path();
    $uri = $_SERVER['REQUEST_URI'];

    if (strpos($uri, '?') !== false) {
      $query = substr($uri, strpos($uri, '?'));
    }

    return $path . $query;
  }

  /**
   * Returns the hosturl
   *
   * ex. http://www.example.com/some/page.html?foo=bar
   *
   * @return string
   */
  public function url(): string
  {
    return $this->name() . $this->relative();
  }
}
