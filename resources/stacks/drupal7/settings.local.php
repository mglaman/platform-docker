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
if ($conf['redis_client_host'] && empty($_SERVER['PLATFORM_DOCKER'])) {
    $conf['redis_client_host'] = trim(shell_exec("docker inspect --format '{{ .NetworkSettings.IPAddress }}' {{ redis_container_name }}"));;
    $conf['redis_client_port'] = trim(shell_exec("docker inspect --format='{{(index (index .NetworkSettings.Ports \"6379/tcp\") 0).HostPort}}' {{ redis_container_name }}"));
}

// Database configuration.
if (empty($_SERVER['PLATFORM_DOCKER'])) {

    $port_cmd = "docker inspect --format='{{(index (index .NetworkSettings.Ports \"3306/tcp\") 0).HostPort}}' {{ container_name }}";
    $port = trim(shell_exec($port_cmd));
    
    $host = '{{ project_domain }}';

    // Default config within Docker container.
    $databases['default']['default'] = array(
      'driver' => 'mysql',
      'host' => $host,
      'port' => $port,
      'username' => 'mysql',
      'password' => 'mysql',
      'database' => 'data',
      'prefix' => '',
    );
}
