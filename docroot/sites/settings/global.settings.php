<?php

use Acquia\Blt\Robo\Common\EnvironmentDetector;

// When the encryption environment variable is not provided (local/ci/etc),
// fake the encryption string so that the site doesn't break.
if (!getenv('STANFORD_ENCRYPT')) {
  putenv("STANFORD_ENCRYPT=" . substr(file_get_contents("$repo_root/salt.txt"), 0, 32));
}

// Modify the deployment identifier string to help with drupal core cache
// invalidation when deploying to Acquia's server. This helps when the Drupal
// container cache hasn't been cleared yet and during deployment the path
// changes from `01live` to `01liveup` and back. On non-Acquia environments, the
// getAhFilesRoot is just `/mnt/gfs/` so it doesn't affect any local or CI envs.
$settings['deployment_identifier'] = $settings['deployment_identifier'] . '-' . substr(md5(EnvironmentDetector::getAhFilesRoot()), 0, 6);

/**
 * @file
 * Generated by BLT. Serves as an example of global includes.
 */

$settings['file_temp_path'] = '/tmp';

// If this is changed, be sure to change it in the
// factory-hooks/post-settings-php/includes.php file
$settings['config_sync_directory'] = $repo_root . "/docroot/profiles/custom/stanford_profile/config/sync";

// Prevent field encrypt module from using eval() for entity hooks.
$settings['field_encrypt.use_eval_for_entity_hooks'] = FALSE;

  // Lock the UI to read_only when on production or test in Acquia.
  if (
    (EnvironmentDetector::isAhProdEnv() || EnvironmentDetector::isAhStageEnv())
    && PHP_SAPI !== 'cli'
  ) {
    $settings['config_readonly'] = TRUE;
    $settings['config_readonly_whitelist_patterns'] = [
      'system.menu.*',
      'core.menu.static_menu_link_overrides',
      'system.theme',
      'google_tag.container.*',
      'google_tag.settings',
      'user.role.*',
      'system.action.user_add_role_action.*',
      'system.action.user_remove_role_action.*',
      'samlauth.authentication',
    ];
    $settings['config_readonly_content_link_providers'] = [
      'menu_link_content',
      'menu_link',
    ];
  }
}

// Block the bots when not on production.
if (!EnvironmentDetector::isAhProdEnv()) {
  $settings['nobots'] = TRUE;
}

/**
 * Include settings files in docroot/sites/settings.
 *
 * If instead you want to add settings to a specific site, see BLT's includes
 * file in docroot/sites/{site-name}/settings/default.includes.settings.php.
 */
$additionalSettingsFiles = [
  __DIR__ . '/configpages.settings.php',
  __DIR__ . '/environment_indicator.settings.php',
  __DIR__ . '/fast404.settings.php',
  __DIR__ . '/google_analytics.settings.php',
  __DIR__ . '/saml.settings.php',
  __DIR__ . '/xmlsitemap.settings.php',
  "$repo_root/keys/secrets.settings.php",
];

foreach ($additionalSettingsFiles as $settingsFile) {
  if (file_exists($settingsFile)) {
    require $settingsFile;
  }
}

if (file_exists(__DIR__ . '/local.settings.php')) {
  require __DIR__ . '/local.settings.php';
}
