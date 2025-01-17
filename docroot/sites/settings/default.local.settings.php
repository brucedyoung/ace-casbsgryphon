<?php

/**
 * @file
 * Local development override configuration feature.
 *
 * The settings in this file will be applied to every multisites.
 */

/**
 * SimpleSaml Workgroup configuration
 */
$config['stanford_ssp.settings'] = [
  'workgroup_api_url' => 'https://workgroupsvc.stanford.edu/v1/workgroups',
  'use_workgroup_api' => TRUE,
  'workgroup_api_cert' => DRUPAL_ROOT . '/../keys/workgroup_api.cert',
  'workgroup_api_key' => DRUPAL_ROOT . '/../keys/workgroup_api.key',
];

// Suppress deprecation and strict errors for PHP on local.
// Disable this if you are trying a new PHP version.
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
