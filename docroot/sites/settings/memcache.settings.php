<?php

use Acquia\Blt\Robo\Common\EnvironmentDetector;

if (EnvironmentDetector::isAhEnv()) {
    // Memcached settings for Acquia Hosting
  if (file_exists(DRUPAL_ROOT . '/sites/default/cloud-memcache-d8+.php')) {
    require(DRUPAL_ROOT . '/sites/default/cloud-memcache-d8+.php');
  }
}
