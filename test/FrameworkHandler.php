<?php

namespace Incept\Framework;

use StdClass;
use PHPUnit\Framework\TestCase;
use UGComponents\Http\Request;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-07-27 at 13:49:45.
 */
class Framework_FrameworkHandler_Test extends TestCase
{
  /**
   * @var FrameworkHandler
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp(): void
  {
    $this->object = new FrameworkHandler;
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown(): void
  {
  }

  /**
   * @covers Incept\Framework\FrameworkHandler::makePayload
   */
  public function testMakePayload()
  {
    $this->object->getRequest()->setStage('foobar', 'barfoo');
    $payload = $this->object->makePayload(true);
    $this->assertEquals('barfoo', $payload['request']->getStage('foobar'));
  }

  /**
   * covers Incept\Framework\FrameworkHandler::error
   */
  public function testError()
  {
    $http = ($this->object)('http')->getErrorProcessor();
    $terminal = ($this->object)('terminal')->getErrorProcessor();

    $this->assertTrue($http->isEmpty());
    $this->assertTrue($terminal->isEmpty());

    $instance = $this->object->error(function() {});
    $this->assertInstanceOf(FrameworkHandler::class, $instance);

    $this->assertFalse($http->isEmpty());
    $this->assertFalse($terminal->isEmpty());
  }

  /**
   * covers Incept\Framework\FrameworkHandler::preprocess
   */
  public function testPreprocess()
  {
    $http = ($this->object)('http')->getPreProcessor();
    $terminal = ($this->object)('terminal')->getPreProcessor();

    $this->assertTrue($http->isEmpty());
    $this->assertTrue($terminal->isEmpty());

    $instance = $this->object->preprocess(function() {});
    $this->assertInstanceOf(FrameworkHandler::class, $instance);

    $this->assertFalse($http->isEmpty());
    $this->assertFalse($terminal->isEmpty());
  }

  /**
   * covers Incept\Framework\FrameworkHandler::postprocess
   */
  public function testPostprocess()
  {
    $http = ($this->object)('http')->getPostProcessor();
    $terminal = ($this->object)('terminal')->getPostProcessor();

    $instance = $this->object->postprocess(function() {});
    $this->assertInstanceOf(FrameworkHandler::class, $instance);

    $this->assertFalse($http->isEmpty());
    $this->assertFalse($terminal->isEmpty());
  }

  /**
   * @covers Incept\Framework\FrameworkHandler::on
   */
  public function testEventSync()
  {
    $http = ($this->object)('http');
    $event = ($this->object)('event');
    $terminal = ($this->object)('terminal');

    $actual = new StdClass();
    $actual->count = 0;
    $this->object->on('foobar', function() use ($actual) {
      $actual->count++;
    });

    $http->emit('foobar');
    $this->assertEquals(1, $actual->count);

    $event->emit('foobar');
    $this->assertEquals(2, $actual->count);

    $terminal->emit('foobar');
    $this->assertEquals(3, $actual->count);
  }
}
