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

$redis_containter_name = "{{ redis_container_name }}";
if ($redis_containter_name) {
    // Set Redis as the default backend for any cache bin not otherwise specified.
    $settings['cache']['default'] = 'cache.backend.redis';
    $settings['redis.connection']['host'] = $redis_containter_name;
    $settings['redis.connection']['port'] = '6379';

    // Apply changes to the container configuration to better leverage Redis.
    // This includes using Redis for the lock and flood control systems, as well
    // as the cache tag checksum. Alternatively, copy the contents of that file
    // to your project-specific services.yml file, modify as appropriate, and
    // remove this line.
    $settings['container_yamls'][] = 'modules/contrib/redis/example.services.yml';

    // Allow the services to work before the Redis module itself is enabled.
    $settings['container_yamls'][] = 'modules/contrib/redis/redis.services.yml';

    // Manually add the classloader path, this is required for the container cache bin definition below
    // and allows to use it without the redis module being enabled.
    $class_loader->addPsr4('Drupal\\redis\\', 'modules/contrib/redis/src');

    // Use redis for container cache.
    // The container cache is used to load the container definition itself, and
    // thus any configuration stored in the container itself is not available
    // yet. These lines force the container cache to use Redis rather than the
    // default SQL cache.
    $settings['bootstrap_container_definition'] = [
        'parameters' => [],
        'services' => [
            'redis.factory' => [
                'class' => 'Drupal\redis\ClientFactory',
            ],
            'cache.backend.redis' => [
                'class' => 'Drupal\redis\Cache\CacheBackendFactory',
                'arguments' => ['@redis.factory', '@cache_tags_provider.container'],
            ],
            'cache.container' => [
                'class' => '\Drupal\redis\Cache\PhpRedis',
                'factory' => ['@cache.backend.redis', 'get'],
                'arguments' => ['container'],
            ],
            'cache_tags_provider.container' => [
                'class' => 'Drupal\redis\Cache\RedisCacheTagsChecksum',
                'arguments' => ['@redis.factory'],
            ],
        ],
    ];

    // Set a fixed prefix so that all requests share the same prefix, even if
    // on different domains.
    $settings['cache_prefix'] = 'prefix_';
}

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
      'username' => 'mysql',
      'password' => 'mysql',
      'database' => 'data',
      'prefix' => '',
      'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',

    );

    if ($redis_containter_name) {
        $settings['redis.connection']['host'] = '127.0.0.1';
        $settings['redis.connection']['port'] = trim(shell_exec("docker inspect --format='{{(index (index .NetworkSettings.Ports \"6379/tcp\") 0).HostPort}}' {{ redis_container_name }}"));
    }
}

// Set the private file path to where a "platform build" command creates one.
$settings['file_private_path'] = '../private';

// Configuration directories.
$config_directories = array(
  CONFIG_ACTIVE_DIRECTORY => '../shared/config/active',
  CONFIG_STAGING_DIRECTORY => '../shared/config/staging',
  CONFIG_SYNC_DIRECTORY => '../shared/config/staging',
);
