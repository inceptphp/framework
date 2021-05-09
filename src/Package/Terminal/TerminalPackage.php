<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Framework\Package\Terminal;

use UGComponents\Terminal\TerminalHandler;
use UGComponents\Terminal\TerminalHelper;

/**
 * Terminal Package
 *
 * @vendor   Incept
 * @package  Package
 * @standard PSR-2
 */
class TerminalPackage extends TerminalHandler
{
  /**
   * @var callable $map
   */
  protected $logs = [];

  /**
   * Outputs colorful (red) message
   *
   * @param *string $message The message
   *
   * @return TerminalPackage
   */
  public function error($message, $die = true): TerminalPackage
  {
    $this->logs[] = ['type' => 'error', 'message' => $message];
    TerminalHelper::error($message, $die);
    return $this;
  }

  /**
   * Returns the errors
   *
   * @return array
   */
  public function getErrors(): array
  {
    $errors = [];

    foreach ($this->logs as $log) {
      if ($log['type'] === 'error') {
        $errors[] = $log['message'];
      }
    }

    return $errors;
  }

  /**
   * Returns the logs
   *
   * @return array
   */
  public function getLogs(): array
  {
    return $this->logs;
  }

  /**
   * Outputs colorful (blue) message
   *
   * @param *string $message The message
   *
   * @return TerminalPackage
   */
  public function info($message): TerminalPackage
  {
    $this->logs[] = ['type' => 'info', 'message' => $message];
    TerminalHelper::info($message);
    return $this;
  }

  /**
   * Queries the user for an
   * input and returns the results
   *
   * @param string    $question The text question
   * @param string|null $default  The default answer
   *
   * @return string
   * @codeCoverageIgnore
   */
  public function input($question, $default = null)
  {
    return TerminalHelper::input($question, $default);
  }

  /**
   * Outputs message
   *
   * @param *string $message The message
   * @param string  $color   Unix color code
   *
   * @return TerminalPackage
   */
  public function output($message, $color = null): TerminalPackage
  {
    $this->logs[] = ['type' => 'general', 'message' => $message];
    TerminalHelper::output($message, $color);
    return $this;
  }

  /**
   * Outputs colorful (green) message
   *
   * @param *string $message The message
   *
   * @return TerminalPackage
   */
  public function success($message): TerminalPackage
  {
    $this->logs[] = ['type' => 'success', 'message' => $message];
    TerminalHelper::success($message);
    return $this;
  }

  /**
   * Outputs colorful (purple) message
   *
   * @param *string $message The message
   *
   * @return TerminalPackage
   */
  public function system($message): TerminalPackage
  {
    $this->logs[] = ['type' => 'system', 'message' => $message];
    TerminalHelper::system($message);
    return $this;
  }

  /**
   * Whether to display the output
   *
   * @param *string $message The message
   *
   * @return TerminalPackage
   */
  public function verbose($display): TerminalPackage
  {
    //if dont display
    if (!$display) {
      TerminalHelper::setMap(function () {
      });
    } else {
      TerminalHelper::setMap(null);
    }

    return $this;
  }

  /**
   * Outputs colorful (orange) message
   *
   * @param *string $message The message
   *
   * @return TerminalPackage
   */
  public function warning($message): TerminalPackage
  {
    $this->logs[] = ['type' => 'warning', 'message' => $message];
    TerminalHelper::warning($message);
    return $this;
  }
}
