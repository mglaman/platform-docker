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
            $path = Platform::rootDir() . '/' . self::PLATFORM_CONFIG;
            $this->config = Yaml::parse(file_get_contents($path));
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

    public static function write($destinationDir = null)
    {
        if (!$destinationDir) {
            $destinationDir = Platform::rootDir();
        }
        return self::instance()->writeConfig($destinationDir);
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

    public function writeConfig($destinationDir = null)
    {
        if (!$destinationDir) {
            $destinationDir = Platform::rootDir();
        }
        return file_put_contents($destinationDir . '/' . self::PLATFORM_CONFIG, Yaml::dump($this->config, 2));
    }
}
