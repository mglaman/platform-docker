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

// Configuration when running drush commands locally.
if (empty($_SERVER['PLATFORM_DOCKER'])) {
    $cmd = "docker inspect --format='{{(index (index .NetworkSettings.Ports \"3306/tcp\") 0).HostPort}}' {{ container_name }}";
    $port = trim(shell_exec($cmd));
    $host = '{{ project_domain }}';

    // Default config within Docker container.
    $databases['default']['default'] = array(
      'driver' => 'mysql',
      'host' => '127.0.0.1',
      'port' => $port,
      'username' => '{{ mysql_user }}',
      'password' => '{{ mysql_password }}',
      'database' => 'data',
      'prefix' => '',
      'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',

    );
}

// Set the private file path to where a "platform build" command creates one.
$settings['file_private_path'] = '../private';

// Configuration directories.
$config_directories = array(
  CONFIG_ACTIVE_DIRECTORY => '../shared/config/active',
  CONFIG_STAGING_DIRECTORY => '../shared/config/staging',
  CONFIG_SYNC_DIRECTORY => '../shared/config/staging',
);
