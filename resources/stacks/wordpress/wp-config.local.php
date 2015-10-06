<?php

define('DB_NAME', $_SERVER['PLATFORM_DB_NAME']);
define('DB_USER', $_SERVER['PLATFORM_DB_USER']);
define('DB_PASSWORD', $_SERVER['PLATFORM_DB_PASSWORD']);

if (!empty($_SERVER['PLATFORM_DOCKER'])) {
    define('DB_HOST', '{{ container_name }}');
}
elseif (empty($_SERVER['PLATFORM_DOCKER'])) {
    $cmd = "docker inspect --format='{{(index (index .NetworkSettings.Ports \"3306/tcp\") 0).HostPort}}' {{ container_name }}";
    $port = trim(shell_exec($cmd));
    define('DB_HOST', "{{ project_domain }}:$port");
}

{{ salts }}

define('WP_DEBUG', false);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', true);
define('SAVEQUERIES', true);
