<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Framework;

use UGComponents\Data\Registry;
use Incept\Framework\Field\FieldRegistry;
use Incept\Framework\Field\FieldInterface;
use Incept\Framework\Fieldset\FieldsetTypes;
use Incept\Framework\Validation\ValidatorRegistry;
use Incept\Framework\Format\FormatterRegistry;
use Incept\Framework\Format\FormatterInterface;

/**
 * Model Fieldset Manager. This was made
 * take advantage of pass-by-ref
 *
 * @vendor   Incept
 * @package  System
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class Fieldset extends Registry
{
  /**
   * @var string $path
   */
  protected static $path;

  /**
   * @var array $fields
   */
  protected $fields = [];

  /**
   * Presets the collection
   *
   * @param array $data The initial data
   */
  public function __construct(array $data = [])
  {
    //load the data in registry
    parent::__construct($data);
    //preload fields by full field name
    $this->fields = $this->getFields();
  }

  /**
   * Archives a fieldset
   *
   * @param ?string $path
   *
   * @return Fieldset
   */
  public function archive(?string $path = null): Fieldset
  {
    if (is_null($path) || !is_dir($path)) {
      $path = static::$path;
    }

    if (is_null($path) || !is_dir($path)) {
      throw SystemException::forFolderNotFound($path);
    }

    $name = $this->getName();

    $source = sprintf('%s/%s.php', $path, $name);
    if (!file_exists($source)) {
      throw SystemException::forFileNotFound($source);
    }

    $destination = sprintf('%s/_%s.php', $path, $name);
    if (file_exists($destination)) {
      throw SystemException::forArchiveExists($destination);
    }

    rename($source, $destination);
    return $this;
  }

  /**
   * Deletes a fieldset
   *
   * @param ?string $path
   *
   * @return Fieldset
   */
  public function delete(?string $path = null): Fieldset
  {
    if (is_null($path) || !is_dir($path)) {
      $path = static::$path;
    }

    if (is_null($path) || !is_dir($path)) {
      throw SystemException::forFolderNotFound($path);
    }

    $source = sprintf('%s/%s.php', $path, $this->getName());
    if (!file_exists($source)) {
      throw SystemException::forFileNotFound($source);
    }

    unlink($source);
    return $this;
  }

  /**
   * Formats the given data to be outputted
   * NOTE: This will also remove data that doesnt have a defined format
   *
   * @param *array $data raw values
   * @param string $type list or detail
   *
   * @return array
   */
  public function format(array $data, string $type = 'list'): array
  {
    //we store the formatted data here because order matters.
    $formatted = [];
    //loop through each field
    foreach ($this->getFields() as $key => $field) {
      //set value because it's possible for formatters to process null values
      $value = null;
      if (isset($data[$key])) {
        $value = $data[$key];
      }

      //load up the formatter
      $formatter = $this->makeFormatter($key, $type);

      //if no formatter
      if (!$formatter) {
        continue;
      }

      $format = $formatter->format($data[$key], $key, $data);

      //if the format is null
      if (is_null($format)) {
        //it was not meant to be added
        continue;
      }

      //add to formatted
      $formatted[$key] = $format;
    }

    //return formatted
    return $formatted;
  }

  /**
   * Validates the given data against the defined validation
   *
   * @param *array $data      values to compare
   * @param bool   $forUpdate if true then wont require fields
   *
   * @return array
   */
  public function getErrors(array $data, bool $forUpdate = false): array
  {
    $errors = [];
    $name = $this->getName();
    //loop through each field
    foreach ($this->getFields() as $key => $config) {
      //make sure there is a value we can compare
      //set value because it's possible for formatters to process null values
      $value = null;
      if (isset($data[$key]) && $data[$key] !== '') {
        $value = $data[$key];
      }

      //load up the field
      $field = $this->makeField($key);

      //this is the field validator, if it's not valid
      if ($field && !$field->valid($value, $key, $data)) {
        //set an error
        $errors[$key] = 'Invalid field format';
        continue;
      }

      //if there is no validation
      if (!isset($config['validation'])
        || !is_array($config['validation'])
        || empty($config['validation'])
      ) {
        //it's obviously valid
        continue;
      }

      //for each validation
      foreach ($config['validation'] as $validation) {
        //if there is no method set
        if (!isset($validation['method'])) {
          //skip
          continue;
        }

        //if this is for an update then dont require
        if ($forUpdate && $validation['method'] === 'required') {
          continue;
        }

        //load up the validator
        $validator = ValidatorRegistry::makeValidator($validation['method']);
        //if no validator
        if (!$validator) {
          //since we cannot determine the validator, just skip it
          continue;
        }

        //make sure we have an error message
        $message = 'Invalid';
        if (isset($validation['message'])) {
          $message = $validation['message'];
        }

        //set parameters
        if (isset($validation['parameters'])) {
          $validator->setParameters($validation['parameters']);
        }

        //if it's not valid
        if (!$validator->valid($value, $key, $data, $name)) {
          //set an error
          $errors[$key] = $message;
          break;
        }
      }
    }

    return $errors;
  }

  /**
   * Returns All fields
   *
   * @return array
   */
  public function getFields(string ...$types): array
  {
    $results = [];
    if (!isset($this->data['fields']) || empty($this->data['fields'])) {
      return $results;
    }

    $table = $this->data['name'];
    foreach ($this->data['fields'] as $field) {
      $key = $table . '_' . $field['name'];
      $field['types'] = $this->getTypes($field);
      //quick way of filtering
      if (!empty($types) && empty(array_intersect($types, $field['types']))) {
        continue;
      }

      $results[$key] = $field;
    }

    return $results;
  }

  /**
   * Returns folder where fieldset is located
   *
   * @returnn ?string
   */
  public static function getFolder(): ?string
  {
    return static::$path;
  }

  /**
   * Renders an array of made fields
   *
   * @param array $data Values of the form
   *
   * @return array
   */
  public function getForm(array $data = []): array
  {
    $form = [];
    //loop through each field
    foreach ($this->getFields() as $key => $config) {
      //make a field
      $field = $this->makeField($key);

      //if no field
      if (!$field) {
        continue;
      }

      //make sure we have a value
      if (!isset($data[$key])) {
        $data[$key] = null;
        //if theres a default
        if (isset($config['default']) && $config['default']) {
          //use the default
          $data[$key] = $config['default'];
        }
      }

      $form[$key] = $field->render($data[$key], $key, $data);
    }

    return $form;
  }

  /**
   * Returns all possible advanced data types given the field
   *
   * @param *array $field
   *
   * @return array
   */
  protected function getTypes(array $field): array
  {
    $types = [];
    $schema = FieldRegistry::getField($field['field']['type']);
    if ($schema) {
      $types = $schema::TYPES;
      //add name as a type
      $types[] = $schema::NAME;
    }

    if (isset($field['list']['format'])
      && $field['list']['format'] !== 'hide'
    ) {
      $types[] = FieldsetTypes::TYPE_LISTED;
    }

    if (isset($field['detail']['format'])
      && $field['detail']['format'] !== 'hide'
    ) {
      $types[] = FieldsetTypes::TYPE_DETAILED;
    }

    if (isset($field['default']) && trim($field['default'])) {
      $types[] = FieldsetTypes::TYPE_DEFAULTED;
    }

    if (isset($field['validation'])) {
      foreach ($field['validation'] as $validation) {
        if ($validation['method'] === 'required') {
          $types[] = FieldsetTypes::TYPE_REQUIRED;
        }

        if ($validation['method'] === 'unique') {
          $types[] = FieldsetTypes::TYPE_UNIQUE;
        }
      }
    }

    return $types;
  }

  /**
   * Instantiate the Fieldet given the name
   *
   * @param *string $name
   * @param ?string $path
   *
   * @return Fieldset
   */
  public static function load(string $name, ?string $path = null): Fieldset
  {
    if (is_null($path) || !is_dir($path)) {
      $path = static::$path;
    }

    if (is_null($path) || !is_dir($path)) {
      throw SystemException::forFolderNotFound($path);
    }

    $source = sprintf('%s/%s.php', $path, $name);
    if (!file_exists($source)) {
      throw SystemException::forFileNotFound($source);
    }

    return new static(include $source);
  }

  /**
   * Returns a field instance
   *
   * @param *string $name  Config field name
   * @param *string $name  Full field name
   *
   * @return ?FieldInterface
   */
  public function makeField(string $name, string $fieldName = null): ?FieldInterface
  {
    //if no suggested field name
    if (!$fieldName) {
      $fieldName = $name;
    }

    //if no field type
    if (!isset($this->fields[$name]['field']['type'])) {
      return null;
    }

    //set the field config short term
    $config = $this->fields[$name]['field'];
    //load up the field
    $field = FieldRegistry::makeField($config['type']);

    //if no field
    if (!$field) {
      return null;
    }

    //set name
    $field->setName($fieldName);

    //set attributes
    if (isset($config['attributes']) && is_array($config['attributes'])) {
      $field->setAttributes($config['attributes']);
    }

    //set options
    if (isset($config['options']) && is_array($config['options'])) {
      $field->setOptions($config['options']);
    }

    //set parameters
    if (isset($config['parameters'])) {
      //make sure parameters is an array
      if (!is_array($config['parameters'])) {
        $config['parameters'] = [ $config['parameters'] ];
      }

      $field->setParameters($config['parameters']);
    }

    return $field;
  }

  /**
   * Returns a field instance
   *
   * @param *string $name Full field name
   * @param string $type  list or detail
   *
   * @return ?FieldInterface
   */
  public function makeFormatter(
    string $name,
    string $type = 'list'
  ): ?FormatterInterface {
    //if no field type
    if (!isset($this->fields[$name][$type]['format'])) {
      return null;
    }

    //set the format config short term
    $config = $this->fields[$name][$type];
    //load up the formatter
    $formatter = FormatterRegistry::makeFormatter($config['format']);

    //if no formatter
    if (!$formatter) {
      return null;
    }

    //set parameters
    $parameters = [];
    if (isset($config['parameters'])) {
      $parameters = $config['parameters'];
      //make sure parameters is an array
      if (!is_array($config['parameters'])) {
        $parameters = [ $config['parameters'] ];
      }
    }

    //determine the format options

    //if there are field options
    if (isset($this->fields[$name]['field']['options'])) {
      //pass it to the formatter
      $formatter->setOptions($this->fields[$name]['field']['options']);
    //if the field type is fieldset
    } else if (isset($this->fields[$name]['field']['type'])
      && $this->fields[$name]['field']['type'] === 'fieldset'
      //and there's parameters
      && isset($this->fields[$name]['field']['parameters'][0])
      && isset($this->fields[$name]['field']['parameters'][1])
    ) {
      //load the fieldset
      $fieldset = Fieldset::load($this->fields[$name]['field']['parameters'][0]);
      //add to parameters
      $parameters[] = $this->fields[$name]['field']['parameters'][0];
      $parameters[] = $this->fields[$name]['field']['parameters'][1];
      $parameters[] = $type;
      //populate options
      $options = [];

      //determine what fields to show
      //if the format type is list
      if ($type === 'list') {
        //get all the fields that are listable
        $fields = $fieldset->getFields(FieldsetTypes::TYPE_LISTED);
      //if the format type is detail
      } else if ($type === 'detail') {
        //get all the fields that are detailable
        $fields = $fieldset->getFields(FieldsetTypes::TYPE_DETAILED);
      } else {
        //just get all the fields
        $fields = $fieldset->getFields();
      }

      //set the options
      foreach ($fields as $key => $field) {
        $options[$key] = $field['label'];
      }

      //pass it to the formatter
      $formatter->setOptions($options);
    }

    //add parameters
    $formatter->setParameters($parameters);

    return $formatter;
  }

  /**
   * Prepares the given data to be saved into an eventual store
   *
   * @param array $data Values to prepare
   *
   * @return array
   */
  public function prepare(array $data, bool $defaults = false): array
  {
    //loop through each field
    foreach ($this->getFields() as $key => $field) {
      //if no value
      if (!isset($data[$key]) || $data[$key] === '') {
        //make a value
        $data[$key] = null;
        //if theres a default
        if ($defaults && isset($field['default']) && $field['default']) {
          //use the default
          $data[$key] = $field['default'];
        }
      }

      //load up the field
      $field = $this->makeField($key);

      //if no field
      if (!$field) {
        continue;
      }

      //prepare the value
      $prepared = $field->prepare($data[$key], $key, $data);

      if (!is_null($prepared)) {
        $data[$key] = $prepared;
      } else {
        //it is false so unset
        unset($data[$key]);
      }
    }

    return $data;
  }

  /**
   * Restores a fieldset
   *
   * @param ?string $path
   *
   * @return Fieldset
   */
  public function restore(?string $path = null): Fieldset
  {
    if (is_null($path) || !is_dir($path)) {
      $path = static::$path;
    }

    if (is_null($path) || !is_dir($path)) {
      throw SystemException::forFolderNotFound($path);
    }

    $name = $this->getName();

    $source = sprintf('%s/_%s.php', $path, $name);
    if (!file_exists($source)) {
      throw SystemException::forArchiveNotFound($source);
    }

    $destination = sprintf('%s/%s.php', $path, $name);
    if (file_exists($destination)) {
      throw SystemException::forFileExists($destination);
    }

    rename($source, $destination);
    return $this;
  }

  /**
   * Saves the fieldset to file
   *
   * @return Fieldset
   */
  public function save(string $path = null): Fieldset
  {
    //if there are fields
    if (is_array($this->get('fields'))) {
      //clean up defaults
      foreach ($this->get('fields') as $i => $field) {
        //unset root
        $this->remove('fields', $i, 'root');
        //convert default
        if (isset($field['default'])) {
          if ($field['default'] === '') {
            $this->set('fields', $i, 'default', null);
          } else if (is_numeric($field['default'])) {
            if (strpos($field['default'], '.') === false) {
              $this->set('fields', $i, 'default', (int) $field['default']);
            } else {
              $this->set('fields', $i, 'default', (float) $field['default']);
            }
          }
        }
      }
    }

    if (is_null($path) || !is_dir($path)) {
      $path = static::$path;
    }

    if (is_null($path) || !is_dir($path)) {
      throw SystemException::forFolderNotFound($path);
    }

    $destination = sprintf('%s/%s.php', $path, $this->getName());

    //if it is not a file
    if (!file_exists($destination)) {
      //make the file
      touch($destination);
      chmod($destination, 0777);
    }

    // at any rate, update the config
    file_put_contents($destination, sprintf(
      "<?php //-->\nreturn %s;",
      var_export($this->data, true)
    ));

    return $this;
  }

  /**
   * Returns fieldset classes that match the given filters
   *
   * @param array $filters Keys can be `path`, `active`, `name`
   *
   * @return array
   */
  public static function search(array $filters = []): array
  {
    $path = static::$path;
    if (isset($filters['path']) && is_dir($filters['path'])) {
      $path = $filters['path'];
    }

    if (is_null($path) || !is_dir($path)) {
      throw SystemException::forFolderNotFound($path);
    }

    $files = scandir($path);

    $active = 1;
    if (isset($filters['active'])) {
      $active = $filters['active'];
    }

    $rows = [];
    foreach ($files as $file) {
      $name = basename($file, '.php');
      if (//if this is not a php file
        strpos($file, '.php') === false
        //or active and this is not active
        || ($active && strpos($file, '_') === 0)
        //or not active and active
        || (!$active && strpos($file, '_') !== 0)
        //or not name
        || (isset($filters['name']) && $filters['name'] !== $name)
      ) {
        continue;
      }

      $rows[$name] = static::load($name);
    }

    return $rows;
  }

  /**
   * Sets folder where fieldset is located
   *
   * @param *string $path
   */
  public static function setFolder(string $path)
  {
    if (!is_dir($path)) {
      throw SystemException::forFolderNotFound($path);
    }

    static::$path = $path;
  }
}
