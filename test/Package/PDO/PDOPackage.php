<?php

namespace Incept\Framework\Package\PDO;

use PHPUnit\Framework\TestCase;

use StdClass;
use PDO;

use Incept\Framework\Framework;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-07-27 at 13:49:45.
 */
class Framework_PDO_PDOPackage_Test extends TestCase
{
  /**
   * @var Framework
   */
  protected $framework;

  /**
   * @var PDOPackage
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp(): void
  {
    $this->framework = new Framework;
    $this->object = new PDOPackage($this->framework);
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown(): void
  {
  }

  /**
   * @covers Incept\Framework\Package\PDO\PDOPackage::loadConfig
   * @covers Incept\Framework\Package\PDO\PDOPackage::register
   * @covers Incept\Framework\Package\PDO\PDOPackage::get
   */
  public function testLoad()
  {
    $actual = $this->framework->package('pdo')->getPackageMethods();
    $this->assertTrue(is_array($actual));

    $this->framework->package('pdo')->register('test', [
      'type' => 'mysql',
      'host' => '127.0.0.1',
      'port' => '3306',
      'name' => 'testing_db',
      'user' => 'root',
      'pass' => ''
    ]);

    $actual = $this->framework->package('pdo')->get('test');
    $this->assertInstanceOf(PDO::class, $actual);
  }
}
