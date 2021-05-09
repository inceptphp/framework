<?php

namespace Incept\Framework;

use PHPUnit\Framework\TestCase;

use Incept\Framework\FrameworkHandler;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-07-27 at 13:49:45.
 */
class Incept_Framework_Schema_Test extends TestCase
{
  /**
   * @var Package
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp(): void
  {
    //this is the OOP version of incept
    $incept = new Framework;
    $testRoot = __DIR__;
    $packageRoot = dirname($testRoot);

    //set the schema folder
    Schema::setFolder($testRoot . '/assets/config/schema');

    //now register storm
    $incept->register('inceptphp/incept-system', $packageRoot);

    //while the above looks like it's unrelated, Fieldset uses FieldHandler
    // and FieldHandler relies on .incept.php to load the default packs...

    //now we can instantiate the object
    $this->object = new Schema(include __DIR__ . '/assets/config/schema/profile.php');
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown(): void
  {
  }

  /**
   * @covers Incept\Framework\Schema::isRestorable
   */
  public function testIsRestorable()
  {
    $this->assertTrue($this->object->isRestorable());
  }

  /**
   * @covers Incept\Framework\Schema::getPrimaryName
   */
  public function testGetPrimaryName()
  {
    $actual = $this->object->getPrimaryName();
    $this->assertEquals('profile_id', $actual);
  }

  /**
   * @covers Incept\Framework\Schema::getRelations
   */
  public function testGetRelations()
  {
    $actual = $this->object->getRelations();
    $this->assertEquals(2, count($actual));

    $actual = $this->object->getRelations(1);
    $this->assertEquals(1, count($actual));

    $actual = $this->object->getRelations(null, 'address');
    $this->assertEquals(1, count($actual));

    $actual = $this->object->getRelations(1, 'file');
    $this->assertEquals(1, count($actual));

    $actual = $this->object->getRelations(1, 'address');
    $this->assertEquals(0, count($actual));
  }

  /**
   * @covers Incept\Framework\Schema::getReverseRelations
   */
  public function testGetReverseRelations()
  {
    $actual = $this->object->getReverseRelations();
    $this->assertEquals(1, count($actual));

    $actual = $this->object->getReverseRelations(2);
    $this->assertEquals(0, count($actual));

    $actual = $this->object->getReverseRelations(1);
    $this->assertEquals(1, count($actual));
  }

  /**
   * @covers Incept\Framework\Schema::getSuggestion
   */
  public function testGetSuggestion()
  {
    $actual = $this->object->getSuggestion([
      'profile_name' => 'foo bar'
    ]);
    $this->assertEquals('foo bar', $actual);
  }

  /**
   * @covers Incept\Framework\Schema::getTypes
   */
  public function testGetTypes()
  {
    $product = new Schema(include __DIR__ . '/assets/config/schema/product.php');
    $fields = $product->getFields();
    $this->assertContains('unique', $fields['product_slug']['types']);
    $this->assertContains('indexable', $fields['product_title']['types']);
    $this->assertContains('searchable', $fields['product_title']['types']);
    $this->assertContains('filterable', $fields['product_status']['types']);
  }

  /**
   * @covers Incept\Framework\Schema::search
   */
  public function testSearch()
  {
    $actual = Schema::search([
      'active' => 1,
      'name' => 'profile',
      'relation' => 'address,2'
    ]);

    $this->assertEquals(1, count($actual));
  }
}