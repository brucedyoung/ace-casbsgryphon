<?php

namespace Drupal\stanford_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Allow a source value to pass through the gate conditionally.
 *
 * TODO: Remove this plugin when a release has been made to migrate_plus.
 *
 * Imagine the source value as wanting to get through the gate.
 * We provide a different source/destination field that acts as the key.
 * We compare to a set of valid keys. We declare whether the key locks
 * the gate or unlocks the gate.
 *
 * This is different from skip_on_value because in that plugin, the source
 * is compared to a value. In this plugin, the source is not compared to
 * anything. The source just wants to get through a gate that is operated
 * by another source/destination field.
 *
 * Unlike skip_on_value, there is no configurable method. The method is
 * essentially restricted to 'process'.
 *
 * The source is not modified if it passes through the gate.
 *
 * @MigrateProcessPlugin(
 *   id = "gate"
 * )
 *
 * Available configuration keys:
 * - use_as_key: source or destination field to be used as the key to the gate.
 * - valid_keys: Value or array of values that are valid keys.
 * - key_direction: lock or unlock.
 *
 * @codingStandardsIgnoreStart
 *
 * Examples:
 *
 * Migrate an email address if an opt_in field is set.
 * @code
 *   field_email:
 *     plugin: gate
 *     source: email
 *     use_as_key: opt_in
 *     valid_keys: TRUE
 *     key_direction: unlock
 * @endcode
 *
 * Colorado requires salary_range data to be displayed. Only migrate
 * salary_range if state is CO. Maybe the data is sloppy and sometimes they use
 * the full state name.
 * @code
 *   field_salary_range:
 *     plugin: gate
 *     source: salary_range
 *     use_as_key: state_abbr
 *     valid_keys:
 *       - CO
 *       - Colorado
 *     key_direction: unlock
 * @endcode
 *
 * While importing baseball players, don't import batting averages for
 * pitchers. The position we use as the key to the gate is stored in a
 * destination field, indicated by @.
 * @code
 *   field_batting_average:
 *     plugin: gate
 *     source: batting_average
 *     use_as_key: @position
 *     valid_keys:
 *       - RHP
 *       - LHP
 *     key_direction: lock
 * @endcode
 *
 * @codingStandardsIgnoreEnd
 *
 * @codeCoverageIgnore
 *   We don't need test coverage for this because it's a direct copy from the
 *   migrate_plus module dev version.
 */
class Gate extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (empty($this->configuration['valid_keys']) && !array_key_exists('valid_keys', $this->configuration)) {
      throw new MigrateException('Gate plugin is missing valid_keys configuration.');
    }
    if (empty($this->configuration['use_as_key']) && !array_key_exists('use_as_key', $this->configuration)) {
      throw new MigrateException('Gate plugin is missing use_as_key configuration.');
    }
    if (empty($this->configuration['key_direction']) && !array_key_exists('key_direction', $this->configuration)) {
      throw new MigrateException('Gate plugin is missing key_direction configuration.');
    }
    if (!in_array($this->configuration['key_direction'], ['lock', 'unlock'], TRUE)) {
      throw new MigrateException('Gate plugin only accepts the following values for key_direction: lock and unlock.');
    }

    $valid_keys = is_array($this->configuration['valid_keys']) ? $this->configuration['valid_keys'] : [$this->configuration['valid_keys']];
    $key = $row->get($this->configuration['use_as_key']);
    $key_is_valid = in_array($key, $valid_keys, TRUE);
    $key_direction = $this->configuration['key_direction'];
    $value_can_pass = ($key_is_valid && $key_direction == 'unlock') || (!$key_is_valid && $key_direction == 'lock');
    if ($value_can_pass) {
      return $value;
    }
    else {
      if ($key_direction == 'lock') {
        $message = sprintf('Processing of destination property %s was skipped: Gate was locked by property %s with value %s.', $destination_property, $this->configuration['use_as_key'], $key);
      }
      else {
        $message = sprintf('Processing of destination property %s was skipped: Gate was not unlocked by property %s with value %s. ', $destination_property, $this->configuration['use_as_key'], $key);
      }
      throw new MigrateSkipProcessException($message);
    }
  }

}
