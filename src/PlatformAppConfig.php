<?php

namespace mglaman\PlatformDocker;

use Symfony\Component\Yaml\Yaml;

/**
 * Reads the .platform.app.yaml configuration file.
 */
class PlatformAppConfig
{
    const PLATFORM_CONFIG = '.platform.app.yaml';

    protected static $instance;
    protected $config = array();

    protected static function instance($refresh = false)
    {
        if (self::$instance === null || $refresh === true) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        if ((empty($this->config)) && file_exists(Platform::rootDir() . '/' . self::PLATFORM_CONFIG)) {
            $path = Platform::rootDir() . '/' . self::PLATFORM_CONFIG;
            $this->config = Yaml::parse(file_get_contents($path));
        }
    }

    public function get($key = null)
    {
        if ($key) {
            return isset($this->config[$key]) ? $this->config[$key] : null;
        }
        return $this->config;
    }

    /**
     * @return string|null
     *   The PHP version string from the platform configuration file. NULL if it can not be determined.
     */
    public function getPhpVersion() {
        list($app, $version) = explode(':', $this->get('type'), 2);
        if (strcasecmp($app, 'php') === 0) {
            return $version;
        }
        return NULL;
    }

    public static function reset()
    {
        return self::instance(true);
    }
}
