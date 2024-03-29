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
class AbstractFormatterTest extends TestCase
{
  /**
   * @var FormatStub
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp(): void
  {
    $this->object = new FormatStub;
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown(): void
  {
  }

  /**
   * @covers Incept\Framework\Format\AbstractFormatter::getConfigFieldset
   */
  public function testGetConfigFieldset()
  {
    $actual = $this->object->getConfigFieldset();
    $this->assertTrue(is_array($actual));
  }

  /**
   * @covers Incept\Framework\Format\AbstractFormatter::setOptions
   */
  public function testSetOptions()
  {
    $actual = $this->object->setOptions(['foo' => 'bar']);
    $this->assertInstanceOf(FormatterInterface::class, $actual);
  }

  /**
   * @covers Incept\Framework\Format\AbstractFormatter::setOptions
   */
  public function testSetParameters()
  {
    $actual = $this->object->setOptions(['foo', 'bar']);
    $this->assertInstanceOf(FormatterInterface::class, $actual);
  }

  /**
   * @covers Incept\Framework\Format\AbstractFormatter::toConfigArray
   */
  public function testToConfigArray()
  {
    $actual = $this->object->toConfigArray();
    $this->assertTrue(is_array($actual));
  }
}

/**
 * Format Stub
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class FormatStub extends AbstractFormatter implements FormatterInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'foo';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Foo Filter';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = FormatTypes::TYPE_GENERAL;

  /**
   * Renders the output format for object forms
   *
   * @param ?mixed  $value
   * @param ?string $name  name of the field formatting
   * @param ?array  $row   the row submitted with the value
   *
   * @return ?string
   */
  public function format(
    $value = null,
    string $name = null,
    array $row = []
  ): ?string {
    return 'foo';
  }
}
