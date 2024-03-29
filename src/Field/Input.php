<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Framework\Field;

use Incept\Framework\Format\FormatTypes;

use DOMDocument;

/**
 * Input Field
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class Input extends AbstractField implements FieldInterface
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
  const NAME = 'input';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Input Field';

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
    $form = new DOMDocument();
    $input = $form->createElement('input');
    $input->setAttribute('class', 'form-control system-form-control');

    foreach ($this->attributes as $key => $attribute) {
      $input->setAttribute($key, (string) $attribute);
    }

    if (static::INPUT_TYPE) {
      $input->setAttribute('type', static::INPUT_TYPE);
    }

    if ($this->name) {
      $input->setAttribute('name', $this->name);
    } else if ($name) {
      $input->setAttribute('name', $name);
    }

    if ($value || is_numeric($value)) {
      $input->setAttribute('value', $value);
    }

    $form->appendChild($input);
    return $form->saveHTML();
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
    $form = new DOMDocument();
    $input = $form->createElement('input');
    $input->setAttribute('class', 'form-control system-form-control');

    foreach ($this->attributes as $key => $attribute) {
      $input->setAttribute($key, $attribute);
    }

    if (static::INPUT_TYPE) {
      $input->setAttribute('type', static::INPUT_TYPE);
    }

    if ($name) {
      $input->setAttribute('name', sprintf('filter[%s]', $name));
    }

    if ($value) {
      $input->setAttribute('value', $value);
    }

    $form->appendChild($input);
    return $form->saveHTML();
  }
}
