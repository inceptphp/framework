<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Framework\Field;

use Incept\Framework\Format\FormatTypes;

use PHPUnit\Framework\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-07-27 at 13:49:45.
 */
class Incept_Field_AbstractField_Test extends TestCase
{
  /**
   * @var FieldStub
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp(): void
  {
    $this->object = new FieldStub;
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown(): void
  {
  }

  /**
   * @covers Incept\Framework\Field\AbstractField::getConfigFieldset
   */
  public function testGetConfigFieldset()
  {
    $actual = $this->object->getConfigFieldset();
    $this->assertTrue(is_array($actual));
  }

  /**
   * @covers Incept\Framework\Field\AbstractField::getName
   * @covers Incept\Framework\Field\AbstractField::setName
   */
  public function testGetName()
  {
    $actual = $this->object->setName('foo');
    $this->assertInstanceOf(FieldInterface::class, $actual);
    $actual = $this->object->getName();
    $this->assertEquals('foo', $actual);
  }

  /**
   * @covers Incept\Framework\Field\AbstractField::prepare
   */
  public function testPrepare()
  {
    $actual = $this->object->prepare('bar', 'foo', ['foo' => 'bar']);
    $this->assertEquals('bar', $actual);
  }

  /**
   * @covers Incept\Framework\Field\AbstractField::renderFilter
   */
  public function testRenderFilter()
  {
    $actual = $this->object->renderFilter('bar', 'foo');
    $this->assertNull($actual);
  }

  /**
   * @covers Incept\Framework\Field\AbstractField::setAttributes
   */
  public function testSetAttributes()
  {
    $actual = $this->object->setAttributes(['foo' => 'bar']);
    $this->assertInstanceOf(FieldInterface::class, $actual);
  }

  /**
   * @covers Incept\Framework\Field\AbstractField::setOptions
   */
  public function testSetOptions()
  {
    $actual = $this->object->setOptions(['foo' => 'bar']);
    $this->assertInstanceOf(FieldInterface::class, $actual);
  }

  /**
   * @covers Incept\Framework\Field\AbstractField::setParameters
   */
  public function testSetParameters()
  {
    $actual = $this->object->setParameters(['foo', 'bar']);
    $this->assertInstanceOf(FieldInterface::class, $actual);
  }

  /**
   * @covers Incept\Framework\Field\AbstractField::setValue
   */
  public function testSetValue()
  {
    $actual = $this->object->setValue('foo');
    $this->assertInstanceOf(FieldInterface::class, $actual);
  }

  /**
   * @covers Incept\Framework\Field\AbstractField::toConfigArray
   */
  public function testToConfigArray()
  {
    $actual = $this->object->toConfigArray();
    $this->assertTrue(is_array($actual));
  }

  /**
   * @covers Incept\Framework\Field\AbstractField::valid
   */
  public function testValid()
  {
    $actual = $this->object->valid('bar', 'foo', ['foo' => 'bar']);
    $this->assertTrue($actual);
  }
}

/**
 * Field Stub
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class FieldStub extends AbstractField implements FieldInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'foo';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Foo Field';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = FieldTypes::TYPE_GENERAL;

  /**
   * @const array FORMATS List of possible formats
   */
  const FORMATS = [
    FormatTypes::TYPE_GENERAL,
    FormatTypes::TYPE_STRING,
    FormatTypes::TYPE_NUMBER,
    FormatTypes::TYPE_DATE,
    FormatTypes::TYPE_HTML,
    FormatTypes::TYPE_JSON,
    FormatTypes::TYPE_CUSTOM
  ];

  /**
   * Renders the field for object forms
   *
   * @param ?mixed  $value
   * @param ?string $name  name of the column in the row
   * @param ?array  $row   the row submitted with the value
   *
   * @return ?string
   */
  public function render(
    $value = null,
    string $name = null,
    array $row = []
  ): ?string {
    return 'bar';
  }
}