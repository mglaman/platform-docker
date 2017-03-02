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
  'username' => 'mysql',
  'password' => 'mysql',
  'database' => 'data',
  'prefix' => '',
);

// Redis configuration.
$conf['redis_client_host'] = '{{ redis_container_name }}'; // Your Redis instance hostname.

// Configuration when running drush commands locally.
if (empty($_SERVER['PLATFORM_DOCKER'])) {

    $conf['redis_client_host'] = '127.0.0.1';
    $conf['redis_client_port'] = trim(shell_exec("docker inspect --format='{{(index (index .NetworkSettings.Ports \"6379/tcp\") 0).HostPort}}' {{ redis_container_name }}"));

    $port_cmd = "docker inspect --format='{{(index (index .NetworkSettings.Ports \"3306/tcp\") 0).HostPort}}' {{ container_name }}";
    $port = trim(shell_exec($port_cmd));

    // Default config within Docker container.
    $databases['default']['default'] = array(
      'driver' => 'mysql',
      'host' => '127.0.0.1',
      'port' => $port,
      'username' => 'mysql',
      'password' => 'mysql',
      'database' => 'data',
      'prefix' => '',
    );
}
