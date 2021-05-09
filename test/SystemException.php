<?php

namespace Incept\Framework;

use PHPUnit\Framework\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-07-28 at 11:36:33.
 */
class Framework_SystemException_Test extends TestCase
{
  /**
   * @var SystemException
   */
  protected $object;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp(): void
  {
    $this->object = new SystemException;
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown(): void
  {
  }

  /**
   * @covers Incept\Framework\SystemException::forArchiveExists
   */
  public function testForArchiveExists()
  {
    $message = null;
    try {
      throw SystemException::forArchiveExists('foo');
    } catch(SystemException $e) {
      $message = $e->getMessage();
    }

    $this->assertEquals('Unable to archive foo, archive already exists.', $message);
  }

  /**
   * @covers Incept\Framework\SystemException::forArchiveNotFound
   */
  public function testForArchiveNotFound()
  {
    $message = null;
    try {
      throw SystemException::forArchiveNotFound('foo');
    } catch(SystemException $e) {
      $message = $e->getMessage();
    }

    $this->assertEquals('Archive foo not found', $message);
  }

  /**
   * @covers Incept\Framework\SystemException::forFileExists
   */
  public function testForFileExists()
  {
    $message = null;
    try {
      throw SystemException::forFileExists('foo');
    } catch(SystemException $e) {
      $message = $e->getMessage();
    }

    $this->assertEquals('Unable to restore foo, file already exists.', $message);
  }

  /**
   * @covers Incept\Framework\SystemException::forFileNotFound
   */
  public function testForFileNotFound()
  {
    $message = null;
    try {
      throw SystemException::forFileNotFound('foo');
    } catch(SystemException $e) {
      $message = $e->getMessage();
    }

    $this->assertEquals('File foo not found', $message);
  }

  /**
   * @covers Incept\Framework\SystemException::forFolderNotFound
   */
  public function testForFolderNotFound()
  {
    $message = null;
    try {
      throw SystemException::forFolderNotFound('foo');
    } catch(SystemException $e) {
      $message = $e->getMessage();
    }

    $this->assertEquals('Folder foo not found', $message);
  }

  /**
   * @covers Incept\Framework\SystemException::forNoRelation
   */
  public function testForNoRelation()
  {
    $message = null;
    try {
      throw SystemException::forNoRelation('foo', 'bar');
    } catch(SystemException $e) {
      $message = $e->getMessage();
    }

    $this->assertEquals('foo has no relation to bar', $message);
  }

  /**
   * @covers Incept\Framework\SystemException::forNoSchema
   */
  public function testForNoSchema()
  {
    $message = null;
    try {
      throw SystemException::forNoSchema();
    } catch(SystemException $e) {
      $message = $e->getMessage();
    }

    $this->assertEquals('Schema is not loaded', $message);
  }
}
