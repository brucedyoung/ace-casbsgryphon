<?php

/**
 * @file
 * Local development override configuration feature.
 */

use Acquia\Blt\Robo\Common\EnvironmentDetector;
use Drupal\Component\Assertion\Handle;

$db_name = 'default';

/**
 * Database configuration.
 */
$databases['default']['default'] = [
  'database' => $db_name,
  'username' => 'user',
  'password' => 'user',
  'host' => 'db',
  'port' => '3306',
  'driver' => 'mysql',
  'prefix' => '',
];

// Use development service parameters.
$settings['container_yamls'][] = EnvironmentDetector::getRepoRoot() . '/docroot/sites/development.services.yml';
$settings['container_yamls'][] = EnvironmentDetector::getRepoRoot() . '/docroot/sites/local.services.yml';
$settings['container_yamls'][] = EnvironmentDetector::getRepoRoot() . '/docroot/sites/blt.development.services.yml';

// Allow access to update.php.
$settings['update_free_access'] = TRUE;

/**
 * Assertions.
 *
 * The Drupal project primarily uses runtime assertions to enforce the
 * expectations of the API by failing when incorrect calls are made by code
 * under development.
 *
 * @see http://php.net/assert
 * @see https://www.drupal.org/node/2492225
 *
 * If you are using PHP 7.0 it is strongly recommended that you set
 * zend.assertions=1 in the PHP.ini file (It cannot be changed from .htaccess
 * or runtime) on development machines and to 0 in production.
 *
 * @see https://wiki.php.net/rfc/expectations
 */
assert_options(ASSERT_ACTIVE, TRUE);
Handle::register();

/**
 * Show all error messages, with backtrace information.
 *
 * In case the error level could not be fetched from the database, as for
 * example the database connection failed, we rely only on this value.
 */
$config['system.logging']['error_level'] = 'verbose';

/**
 * Disable CSS and JS aggregation.
 */
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;

/**
 * Disable the render cache (this includes the page cache).
 *
 * Note: you should test with the render cache enabled, to ensure the correct
 * cacheability metadata is present. However, in the early stages of
 * development, you may want to disable it.
 *
 * This setting disables the render cache by using the Null cache back-end
 * defined by the development.services.yml file above.
 *
 * Do not use this setting until after the site is installed.
 */
// $settings['cache']['bins']['render'] = 'cache.backend.null';
/**
 * Disable Dynamic Page Cache.
 *
 * Note: you should test with Dynamic Page Cache enabled, to ensure the correct
 * cacheability metadata is present (and hence the expected behavior). However,
 * in the early stages of development, you may want to disable it.
 */
// $settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
/**
 * Allow test modules and themes to be installed.
 *
 * Drupal ignores test modules and themes by default for performance reasons.
 * During development it can be useful to install test extensions for debugging
 * purposes.
 */
$settings['extension_discovery_scan_tests'] = FALSE;


/**
 * Configure static caches.
 *
 * Note: you should test with the config, bootstrap, and discovery caches
 * enabled to test that metadata is cached as expected. However, in the early
 * stages of development, you may want to disable them. Overrides to these bins
 * must be explicitly set for each bin to change the default configuration
 * provided by Drupal core in core.services.yml.
 * See https://www.drupal.org/node/2754947
 */

// $settings['cache']['bins']['bootstrap'] = 'cache.backend.null';
// $settings['cache']['bins']['discovery'] = 'cache.backend.null';
// $settings['cache']['bins']['config'] = 'cache.backend.null';
/**
 * Enable access to rebuild.php.
 *
 * This setting can be enabled to allow Drupal's php and database cached
 * storage to be cleared via the rebuild.php page. Access to this page can also
 * be gained by generating a query string from rebuild_token_calculator.sh and
 * using these parameters in a request to rebuild.php.
 */
$settings['rebuild_access'] = FALSE;

/**
 * Skip file system permissions hardening.
 *
 * The system module will periodically check the permissions of your site's
 * site directory to ensure that it is not writable by the website user. For
 * sites that are managed with a version control system, this can cause problems
 * when files in that directory such as settings.php are updated, because the
 * user pulling in the changes won't have permissions to modify files in the
 * directory.
 */
$settings['skip_permissions_hardening'] = TRUE;

/**
 * Files paths.
 */
$settings['file_private_path'] = EnvironmentDetector::getRepoRoot() . '/files-private/default';
/**
 * Site path.
 *
 * @var $site_path
 * This is always set and exposed by the Drupal Kernel.
 */
// phpcs:ignore
$settings['file_public_path'] = 'sites/' . EnvironmentDetector::getSiteName($site_path) . '/files';

/**
 * Trusted host configuration.
 *
 * See full description in default.settings.php.
 */
$settings['trusted_host_patterns'] = [
  '^.+$',
];

// /////////////////////////////////////////////////////////////////////////////
//
// DYNAMIC SYNC DIRECTORY
//
// Due to a core bug it is only possible to have one `sync` directory per
// project. Acquia's recommendation is to use config_split's but because we have
// shared code outside of each stack this does not work well for us.
//
// SEE:
// BLT/Acquia Page: https://support.acquia.com/hc/en-us/articles/360024009393
// Core issue Page: https://www.drupal.org/docs/8/configuration-management/changing-the-storage-location-of-the-sync-directory
//
// /////////////////////////////////////////////////////////////////////////////
if (isset($_SERVER['argv'])) {
  foreach ($_SERVER['argv'] as $arg) {
    if (!empty($arg) && ($profile = glob(DRUPAL_ROOT . "/profiles/*/$arg/config/sync/core.extension.yml"))) {
      $settings['config_sync_directory'] = dirname($profile[0]);
      $config_directories['sync'] = dirname($profile[0]);
      break;
    }
  }
}

// If a profile was not found.
if (!isset($profile) || empty($profile)) {

  $default_database = $databases['default']['default'];
  $database = $default_database['database'];
  $username = $default_database['username'];
  $password = $default_database['password'];
  $host = $default_database['host'];
  $port = $default_database['port'];
  $conn = NULL;

  try {
    $conn = new PDO("mysql:host=$host;dbname=$database;port=$port", $username, $password);

    // Set the PDO error mode to exception.
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Terminal execution to fetch the profile from the core.extension config
    // object.
    $command = "SELECT data FROM config WHERE name='core.extension'";
    $query = $conn->query($command)->fetchAll(PDO::FETCH_ASSOC);
  }
  catch (PDOException $e) {
    // Nada.
  }

  if (isset($query[0]['data'])) {
    $data = unserialize($query[0]['data']);
    $profile = $data['profile'];
  }

  if (is_string($profile) && is_file(DRUPAL_ROOT . "/profiles/custom/$profile/config/sync/core.extension.yml")) {
    $settings['config_sync_directory'] = "profiles/custom/$profile/config/sync";
    $config_directories['sync'] = "profiles/custom/$profile/config/sync";
  }
}
