<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/29/15
 * Time: 10:01 PM
 */

namespace mglaman\PlatformDocker\Utils\Platform;


use Symfony\Component\Yaml\Yaml;

class Config
{
    const PLATFORM_CONFIG = '.platform-project';

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
            $this->config = Yaml::parse(Platform::rootDir() . '/' . self::PLATFORM_CONFIG);
        }
        return $this->config;
    }

    public static function get($key = null)
    {
        return self::instance()->getConfig($key);
    }

    public static function set($key, $value)
    {
        return self::instance()->setConfig($key, $value);
    }

    public static function write()
    {
        return self::instance()->writeConfig();
    }

    public function getConfig($key = null)
    {
        if ($key) {
            return $this->config[$key];
        }
        return $this->config;
    }

    public function setConfig($key, $value)
    {
        $this->config[$key] = $value;
        return $this;
    }

    public static function reset()
    {
        return self::instance(true);
    }

    public function writeConfig()
    {
        return file_put_contents(Platform::rootDir() . '/.platform-project', Yaml::dump($this->config, 2));
    }
}
