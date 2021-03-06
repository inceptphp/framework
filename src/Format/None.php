<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Framework\Format;

use Incept\Framework\Format\AbstractFormatter;
use Incept\Framework\Format\FormatterInterface;
use Incept\Framework\Format\FormatTypes;

/**
 * No Custom Format
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class None extends AbstractFormatter implements FormatterInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'none';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'No Filter';

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
    if (is_array($value)) {
      return 'Array()';
    }

    if (is_object($value)) {
      return 'Object()';
    }

    return $value;
  }
}
