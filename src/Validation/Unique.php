<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Framework\Validation;

use Incept\Framework\Validation\AbstractValidator;
use Incept\Framework\Validation\ValidatorInterface;
use Incept\Framework\Validation\ValidationTypes;
use Incept\Framework\Schema;

use Incept\Framework\Framework;

/**
 * Unique Validator
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class Unique extends AbstractValidator implements ValidatorInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'unique';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Unique';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = ValidationTypes::TYPE_GENERAL;

  /**
   * Renders the executes the validation for object forms
   *
   * @param ?mixed  $value
   * @param ?string $name   name of the field validating
   * @param ?array  $row    the row submitted with the value
   * @param ?string $scehma
   *
   * @return bool
   */
  public function valid(
    $value = null,
    string $name = null,
    array $row = [],
    string $schema = null
  ): bool {
    //if it's null, allow it
    if (is_null($value)) {
      return true;
    }

    //dont try this at home kids
    $item = incept('event')->call('system-object-detail', [
      'schema' => $schema,
      $name => $value
    ]);

    //if no item was found
    if (!$item) {
      return true;
    }

    //an item was found
    $schema = Schema::load($schema);
    $primary = $schema->getPrimaryName();

    //if the primary name is set in the row and item
    return isset($row[$primary], $item[$primary])
      //and they are equal to each other
      && $item[$primary] == $row[$primary];
      //it means they found the exact item, so it's valid
  }
}
