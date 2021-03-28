<?php

namespace Incept\Framework\Package\Http;

use StdClass;
use PHPUnit\Framework\TestCase;

use Incept\Framework\FrameworkHandler;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-07-27 at 13:49:45.
 */
class Framework_Http_HttpPackage_Test extends TestCase
{
  /**
   * @var HttpPackage
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp(): void
  {
    $handler = new FrameworkHandler;
    $this->object = new HttpPackage($handler);
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown(): void
  {
  }

  /**
   * @covers Incept\Framework\Package\Http\HttpPackage::all
   */
  public function testAll()
  {
    $instance = $this->object->all('/foo/bar', function() {});
    $this->assertInstanceOf(HttpPackage::class, $instance);
  }

  /**
   * @covers Incept\Framework\Package\Http\HttpPackage::delete
   */
  public function testDelete()
  {
    $instance = $this->object->delete('/foo/bar', function() {});
    $this->assertInstanceOf(HttpPackage::class, $instance);
  }

  /**
   * @covers Incept\Framework\Package\Http\HttpPackage::get
   */
  public function testGet()
  {
    $instance = $this->object->get('/foo/bar', function() {});
    $this->assertInstanceOf(HttpPackage::class, $instance);
  }

  /**
   * @covers Incept\Framework\Package\Http\HttpPackage::post
   */
  public function testPost()
  {
    $instance = $this->object->post('/foo/bar', function() {});
    $this->assertInstanceOf(HttpPackage::class, $instance);
  }

  /**
   * @covers Incept\Framework\Package\Http\HttpPackage::put
   */
  public function testPut()
  {
    $instance = $this->object->put('/foo/bar', function() {});
    $this->assertInstanceOf(HttpPackage::class, $instance);
  }

  /**
   * @covers Incept\Framework\Package\Http\HttpPackage::route
   */
  public function testRoute()
  {
    $instance = $this->object->route('foobar', '/foo/bar', function() {});
    $this->assertInstanceOf(HttpPackage::class, $instance);

    $instance = $this->object->route('foobar', '/foo/bar', 'foobar');
    $this->assertInstanceOf(HttpPackage::class, $instance);

    $instance = $this->object->route('foobar', '/foo/bar', 'foobar', 'foobar2');
    $this->assertInstanceOf(HttpPackage::class, $instance);
  }
}
