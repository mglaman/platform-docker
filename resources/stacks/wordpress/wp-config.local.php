<?php

define('DB_NAME', 'data');
define('DB_USER', 'mysql');
define('DB_PASSWORD', 'mysql');

if (!empty($_SERVER['PLATFORM_DOCKER'])) {
    define('DB_HOST', '{{ container_name }}');
}
elseif (empty($_SERVER['PLATFORM_DOCKER'])) {
    $cmd = "docker inspect --format='{{(index (index .NetworkSettings.Ports \"3306/tcp\") 0).HostPort}}' {{ container_name }}";
    $port = trim(shell_exec($cmd));
    $host_cmd = "docker inspect --format='{{ .NetworkSettings.Gateway }}' {{ container_name }}";
    $host = trim(shell_exec($host_cmd));
    define('DB_HOST', "$host:$port");
}

{{ salts }}

define('WP_DEBUG', false);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', true);
define('SAVEQUERIES', true);
