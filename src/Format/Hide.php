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
 * Hide Column
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
class Hide extends AbstractFormatter implements FormatterInterface
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'hide';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Do Not Show';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = FormatTypes::TYPE_CUSTOM;

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
  ): ?string
  {
    return null;
  }
}
