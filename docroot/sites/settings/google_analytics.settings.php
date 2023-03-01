<?php
use Acquia\Blt\Robo\Common\EnvironmentDetector;

// Do not provide an account for GA on non-prod.
if (!EnvironmentDetector::isAhProdEnv()) {
  $config['google_analytics.settings']['account'] = '';
}

// Provide an account value for testing in CircleCi.
if (getenv('CIRCLECI')) {
  $config['google_analytics.settings']['account'] = 'UA-123456-12';
}
