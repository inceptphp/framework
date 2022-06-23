<?php

namespace Incept\Framework;

use PHPUnit\Framework\TestCase;

use Incept\Framework\Field\AbstractField;
use Incept\Framework\Field\FieldInterface;
use Incept\Framework\Field\FieldRegistry;
use Incept\Framework\Field\FieldTypes;

use Incept\Framework\Format\FormatTypes;
use Incept\Framework\Format\AbstractFormatter;
use Incept\Framework\Format\FormatterInterface;
use Incept\Framework\Format\FormatterRegistry;

use Incept\Framework\Validation\ValidatorRegistry;
use Incept\Framework\Validation\AbstractValidator;
use Incept\Framework\Validation\ValidatorInterface;
use Incept\Framework\Validation\ValidationTypes;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-07-27 at 13:49:45.
 */
class Package_System_Fieldset_Test extends TestCase
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
    //set the schema folder
    Fieldset::setFolder(__DIR__ . '/assets/config/schema');
    //now we can instantiate the object
    $this->object = new Fieldset(include __DIR__ . '/assets/config/foo.php');
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   */
  protected function tearDown(): void
  {
  }

  /**
   * @covers Incept\Framework\Fieldset::format
   */
  public function testFormat()
  {
    $data = $this->object->format([
      'foo_title' => 'foo',
      'foo_formula' => 'foo',
      'foo_active' => 'foo',
      'foo_created' => 'foo',
      'foo_updated' => 'foo'
    ]);

    $this->assertEquals('foo', $data['foo_title']);
    $this->assertEquals('foo', $data['foo_created']);
    $this->assertEquals('foo', $data['foo_updated']);
  }

  /**
   * @covers Incept\Framework\Fieldset::getErrors
   */
  public function testGetErrors()
  {
    $errors = $this->object->getErrors([
      'foo_title' => 'foo',
      'foo_formula' => 'foo',
      'foo_active' => 'foo',
      'foo_created' => 'foo',
      'foo_updated' => 'foo'
    ]);

    $this->assertEmpty($errors);

    $errors = $this->object->getErrors([
      'foo_title' => null,
      'foo_formula' => null,
      'foo_active' => null,
      'foo_created' => null,
      'foo_updated' => null
    ]);

    $this->assertTrue(isset($errors['foo_title']));

    $errors = $this->object->getErrors([
      'foo_title' => 0,
      'foo_formula' => null,
      'foo_active' => null,
      'foo_created' => null,
      'foo_updated' => null
    ]);

    $this->assertTrue(isset($errors['foo_title']));
  }

  /**
   * @covers Incept\Framework\Fieldset::getFields
   * @covers Incept\Framework\Fieldset::getTypes
   */
  public function testGetFields()
  {
    $fields = $this->object->getFields();
    $this->assertTrue(isset($fields['foo_title']));
    $this->assertTrue(isset($fields['foo_formula']));
    $this->assertTrue(isset($fields['foo_active']));
    $this->assertTrue(isset($fields['foo_created']));
    $this->assertTrue(isset($fields['foo_updated']));

    $fields = $this->object->getFields('string');
    $this->assertTrue(isset($fields['foo_title']));
    $this->assertTrue(!isset($fields['foo_formula']));
    $this->assertTrue(!isset($fields['foo_active']));
    $this->assertTrue(!isset($fields['foo_created']));
    $this->assertTrue(!isset($fields['foo_updated']));

    $fields = $this->object->getFields('date', 'datetime', 'time');
    $this->assertTrue(!isset($fields['foo_title']));
    $this->assertTrue(!isset($fields['foo_formula']));
    $this->assertTrue(!isset($fields['foo_active']));
    $this->assertTrue(isset($fields['foo_created']));
    $this->assertTrue(isset($fields['foo_updated']));
  }

  /**
   * @covers Incept\Framework\Fieldset::getFolder
  * @covers Incept\Framework\Fieldset::setFolder
   */
  public function testGetFolder()
  {
    $actual = __DIR__ . '/assets/config/schema';
    //set the schema folder
    Fieldset::setFolder($actual);
    $this->assertEquals($actual, Fieldset::getFolder());
  }

  /**
   * @covers Incept\Framework\Fieldset::getForm
   */
  public function testGetForm()
  {
    $form = $this->object->getForm();
    $this->assertTrue(!!trim((string) $form['foo_title']));
    $this->assertTrue(!trim((string) $form['foo_formula']));
    $this->assertTrue(!trim((string) $form['foo_active']));
    $this->assertTrue(!trim((string) $form['foo_created']));
    $this->assertTrue(!trim((string) $form['foo_updated']));
  }

  /**
   * @covers Incept\Framework\Fieldset::load
   */
  public function testLoad()
  {
    $actual = Fieldset::load('profile')->get('name');
    $this->assertEquals('profile', $actual);
  }

  /**
   * @covers Incept\Framework\Fieldset::makeField
   */
  public function testMakeField()
  {
    $actual = $this->object->makeField('foo_title')->prepare('hi');
    $this->assertEquals('hi', $actual);
  }

  /**
   * @covers Incept\Framework\Fieldset::makeFormatter
   */
  public function testMakeFormatter()
  {
    $actual = $this->object->makeFormatter('foo_active')->format(1);
    $this->assertNull($actual);
  }

  /**
   * @covers Incept\Framework\Fieldset::prepare
   */
  public function testPrepare()
  {
    $data = $this->object->prepare([
      'foo_title' => null,
      'foo_formula' => null,
      'foo_active' => null,
      'foo_created' => null,
      'foo_updated' => null
    ], true);

    $this->assertTrue(!isset($data['foo_title']));
    $this->assertEquals('bar', $data['foo_formula']);
    $this->assertEquals(1, $data['foo_active']);
    $this->assertStringContainsString(date('Y-m'), $data['foo_created']);
    $this->assertStringContainsString(date('Y-m'), $data['foo_updated']);

    $data = $this->object->prepare([
      'foo_title' => 'foo',
      'foo_formula' => 'foo',
      'foo_active' => 'foo',
      'foo_created' => 'foo',
      'foo_updated' => 'foo'
    ], true);

    $this->assertEquals('foo', $data['foo_title']);
    $this->assertEquals('foo', $data['foo_formula']);
    $this->assertEquals(1, $data['foo_active']);
    $this->assertStringContainsString(date('Y-m'), $data['foo_created']);
    $this->assertStringContainsString(date('Y-m'), $data['foo_updated']);
  }

  /**
   * @covers Incept\Framework\Fieldset::search
   */
  public function testSearch()
  {
    $actual = Fieldset::search([
      'active' => 1,
      'name' => 'profile'
    ]);

    $this->assertEquals(1, count($actual));
  }

  /**
   * @covers Incept\Framework\Fieldset::save
   */
  public function testSave()
  {
    $source = __DIR__ . '/assets/config/foo.php';
    $destination = __DIR__ . '/assets/config/schema/foo.php';

    if (file_exists($destination)) {
      unlink($destination);
    }

    $fieldset = new Fieldset(include $source);
    $fieldset->save();

    $this->assertTrue(file_exists($destination));
  }

  /**
   * @covers Incept\Framework\Fieldset::archive
   */
  public function testArchive()
  {
    $source = __DIR__ . '/assets/config/schema/foo.php';
    $destination = __DIR__ . '/assets/config/schema/_foo.php';

    $fieldset = new Fieldset(include $source);
    $fieldset->archive();

    $this->assertFalse(file_exists($source));
    $this->assertTrue(file_exists($destination));
  }

  /**
   * @covers Incept\Framework\Fieldset::restore
   */
  public function testRestore()
  {
    $source = __DIR__ . '/assets/config/schema/_foo.php';
    $destination = __DIR__ . '/assets/config/schema/foo.php';

    $fieldset = new Fieldset(include $source);
    $fieldset->restore();

    $this->assertFalse(file_exists($source));
    $this->assertTrue(file_exists($destination));
  }

  /**
   * @covers Incept\Framework\Fieldset::delete
   */
  public function testDelete()
  {
    $source = __DIR__ . '/assets/config/schema/foo.php';

    $fieldset = new Fieldset(include $source);
    $fieldset->delete();

    $this->assertFalse(file_exists($source));
  }
}

/**
 * Input Field
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class FieldStub extends AbstractField implements FieldInterface
{
  /**
   * @const bool HAS_ATTRIBUTES Whether or not to show attribute fieldset
   * on the schema form if the field was chosen
   */
  const HAS_ATTRIBUTES = true;

  /**
   * @const ?string INPUT_TYPE HTML input field type
   */
  const INPUT_TYPE = null;

  /**
   * @const bool IS_FILTERABLE Whether or not to enable the filterable checkbox
   * on the schema form if the field was chosen
   */
  const IS_FILTERABLE = true;

  /**
   * @const bool IS_SEARCHABLE Whether or not to enable the searchable checkbox
   * on the schema form if the field was chosen
   */
  const IS_SEARCHABLE = true;

  /**
   * @const bool IS_SORTABLE Whether or not to enable the sortable checkbox
   * on the schema form if the field was chosen
   */
  const IS_SORTABLE = true;

  /**
   * @const string NAME Config name
   */
  const NAME = 'foo';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Foo Field';

  /**
   * @const array TYPES List of possible data types
   */
  const TYPES = [
    FieldTypes::TYPE_STRING
  ];

  /**
   * @const array FORMATS List of possible formats
   */
  const FORMATS = [
    FormatTypes::TYPE_GENERAL,
    FormatTypes::TYPE_STRING,
    FormatTypes::TYPE_NUMBER,
    FormatTypes::TYPE_DATE,
    FormatTypes::TYPE_HTML,
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
    return 'foo';
  }

  /**
   * Renders the field for filter forms
   *
   * @param ?mixed  $value
   * @param ?string $name  name of the column in the row
   *
   * @return ?string
   */
  public function renderFilter($value = null, string $name = null): ?string
  {
    $data = [
      'name' => $this->name,
      'value' => $value,
      'attributes' => $this->attributes,
      'options' => $this->options,
      'parameters' => $this->parameters
    ];

    if (static::INPUT_TYPE) {
      $data['attributes']['type'] = static::INPUT_TYPE;
    }

    $template = incept('handlebars')->compile(
      file_get_contents(__DIR__ . '/template/filter/input.html')
    );
    return $template($data);
  }
}

/**
 * Lower Case Format
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
  const LABEL = 'Foo';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = FormatTypes::TYPE_STRING;

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

/**
 * Characters Less Than Validator
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class ValidStub extends AbstractValidator implements ValidatorInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'foo';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Foo Validation';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = ValidationTypes::TYPE_STRING;

  /**
   * Renders the executes the validation for object forms
   *
   * @param ?mixed  $value
   * @param ?string $name  name of the field validating
   * @param ?array  $row   the row submitted with the value
   *
   * @return bool
   */
  public function valid($value = null, string $name = null, array $row = []): bool
  {
    return !!$value;
  }
}

//register fields
FieldRegistry::register(FieldStub::class);

//register validators
ValidatorRegistry::register(ValidStub::class);

//register formats
FormatterRegistry::register(FormatStub::class);
