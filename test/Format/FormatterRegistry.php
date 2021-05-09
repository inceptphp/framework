<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Framework\Format;

use PHPUnit\Framework\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-07-27 at 13:49:45.
 */
class Incept_Format_FormatterRegistry_Test extends TestCase
{
  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp(): void
  {
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown(): void
  {
  }

  /**
   * @covers Incept\Framework\Format\FormatterRegistry::getFormatter
   */
  public function testGetFormatter()
  {
    $actual = new (FormatterRegistry::getFormatter('foo'));
    $this->assertInstanceOf(FormatterInterface::class, $actual);
  }

  /**
   * @covers Incept\Framework\Format\FormatterRegistry::getFormatters
   */
  public function testGetFormatters()
  {
    $actual = FormatterRegistry::getFormatters();
    $this->assertTrue(is_array($actual));
  }

  /**
   * @covers Incept\Framework\Format\FormatterRegistry::makeFormatter
   */
  public function testMakeFormatter()
  {
    $actual = FormatterRegistry::makeFormatter('foo');
    $this->assertInstanceOf(FormatterInterface::class, $actual);
  }

  /**
   * @covers Incept\Framework\Format\FormatterRegistry::register
   */
  public function testRegister()
  {
    $actual = FormatterRegistry::register('foo::class');
    $this->assertFalse($actual);

    $actual = FormatterRegistry::register(None::class);
    $this->assertTrue($actual);
  }
}
