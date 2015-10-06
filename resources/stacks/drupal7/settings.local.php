<?php

/**
 * Salt for one-time login links and cancel links, form tokens, etc.
 *
 * If this variable is empty, a hash of the serialized database credentials
 * will be used as a fallback salt.
 */
$drupal_hash_salt = '{{ salt }}';

// Database configuration.
$databases['default']['default'] = array(
  'driver' => 'mysql',
  'host' => '{{ container_name }}',
  'username' => $_SERVER['PLATFORM_DB_NAME'],
  'password' => $_SERVER['PLATFORM_DB_USER'],
  'database' => $_SERVER['PLATFORM_DB_PASSWORD'],
  'prefix' => '',
);
// Database configuration.
if (empty($_SERVER['PLATFORM_DOCKER'])) {
    $cmd = "docker inspect --format='{{(index (index .NetworkSettings.Ports \"3306/tcp\") 0).HostPort}}' {{ container_name }}";
    $port = trim(shell_exec($cmd));
    // Default config within Docker container.
    $databases['default']['default'] = array(
      'driver' => 'mysql',
      'host' => '{{ project_domain }}',
      'port' => $port,
      'username' => $_SERVER['PLATFORM_DB_NAME'],
      'password' => $_SERVER['PLATFORM_DB_USER'],
      'database' => $_SERVER['PLATFORM_DB_PASSWORD'],
      'prefix' => '',
    );
}
