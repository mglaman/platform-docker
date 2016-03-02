<?php

/**
 * Salt for one-time login links and cancel links, form tokens, etc.
 *
 * If this variable is empty, a hash of the serialized database credentials
 * will be used as a fallback salt.
 */
$settings['hash_salt'] = '{{ salt }}';

$settings['file_chmod_directory'] = 0775;
$settings['file_chmod_file'] = 0664;

/**
 * Use local services definition file.
 */
$settings['container_yamls'][] = __DIR__ . '/services.yml';

// Database configuration.
$databases['default']['default'] = array(
  'driver' => 'mysql',
  'host' => '{{ container_name }}',
  'username' => 'mysql',
  'password' => 'mysql',
  'database' => 'data',
  'prefix' => '',
  'port' => 3306,
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
);

// Database configuration.
if (empty($_SERVER['PLATFORM_DOCKER'])) {
    $cmd = "docker inspect --format='{{(index (index .NetworkSettings.Ports \"3306/tcp\") 0).HostPort}}' {{ container_name }}";
    $port = trim(shell_exec($cmd));
    $host_cmd = "docker inspect --format='{{ .NetworkSettings.Gateway }}' {{ container_name }}";
    $host = trim(shell_exec($host_cmd));

    // Default config within Docker container.
    $databases['default']['default'] = array(
      'driver' => 'mysql',
      'host' => $host,
      'port' => $port,
      'username' => 'mysql',
      'password' => 'mysql',
      'database' => 'data',
      'prefix' => '',
      'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',

    );
}

// Configuration directories.
$config_directories = array(
  CONFIG_ACTIVE_DIRECTORY => '../../../shared/config/active',
  CONFIG_STAGING_DIRECTORY => '../../../shared/config/staging',
  CONFIG_SYNC_DIRECTORY => '../../../shared/config/staging',
);
