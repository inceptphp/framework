<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Framework\Validation;

use Incept\Framework\Field\FieldRegistry;

/**
 * RegEx Validator
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class Expression extends AbstractValidator implements ValidatorInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'expression';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Valid Expression';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = ValidationTypes::TYPE_CUSTOM;

  /**
   * When they choose this validator in a schema form,
   * we need to know what parameters to ask them for
   *
   * @return array
   */
  public static function getConfigFieldset(): array
  {
    return [
      FieldRegistry::makeField('input')
        ->setName('{NAME}[parameters][0]')
        ->setAttributes([
          'type' => 'text',
          'placeholder' => 'Enter Regular Expression',
          'required' => 'required'
        ])
    ];
  }

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
    return isset($this->parameters[0]) && preg_match($this->parameters[0], $value);
  }
}
