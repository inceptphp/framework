<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Framework\Format;

use Incept\Framework\Format\FormatTypes;

/**
 * Abstractly defines a formatter
 *
 * @vendor   Incept
 * @package  System
 * @standard PSR-2
 */
abstract class AbstractFormatter
{
  /**
   * @const string NAME Config name
   */
  const NAME = 'unknown';

  /**
   * @const string LABEL Config label
   */
  const LABEL = 'Unknown';

  /**
   * @const string TYPE Config Type
   */
  const TYPE = FormatTypes::TYPE_GENERAL;

  /**
   * @var array $options Hash of options to consider when rendering
   */
  protected $options = [];

  /**
   * @var array $parameters List of parametrs to consider when formatting
   */
  protected $parameters = [];

  /**
   * Renders the output format for object forms
   *
   * @param ?mixed  $value
   * @param ?string $name  name of the field formatting
   * @param ?array  $row   the row submitted with the value
   *
   * @return ?string
   */
  abstract public function format(
    $value = null,
    string $name = null,
    array $row = []
  ): ?string;

  /**
   * When they choose this format in a schema form,
   * we need to know what parameters to ask them for
   *
   * @return array
   */
  public static function getConfigFieldset(): array
  {
    return [];
  }

  /**
   * Sets the options that will be
   * considered when rendering the template
   *
   * These options are normally from the field options
   * passed over to the format
   *
   * @param *array $options
   *
   * @return FormatterInterface
   */
  public function setOptions(array $options): FormatterInterface
  {
    $this->options = $options;
    return $this;
  }

  /**
   * Sets the parameters that will be
   * considered when rendering the template
   *
   * @param *array $parameters
   *
   * @return FormatterInterface
   */
  public function setParameters(array $parameters): FormatterInterface
  {
    $this->parameters = $parameters;
    return $this;
  }

  /**
   * Converts instance to an array
   *
   * @return array
   */
  public static function toConfigArray(): array
  {
    return [
      'name' => static::NAME,
      'type' => static::TYPE,
      'label' => static::LABEL
    ];
  }
}
