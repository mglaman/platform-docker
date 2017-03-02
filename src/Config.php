<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/29/15
 * Time: 10:01 PM
 */

namespace mglaman\PlatformDocker;

use Symfony\Component\Yaml\Yaml;

class Config
{
    use YamlConfigReader;

    const PLATFORM_CONFIG = '.platform-project';

    protected function getConfigFilePath()
    {
        return self::PLATFORM_CONFIG;
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

    public function setConfig($key, $value)
    {
        $this->config[$key] = $value;
        return $this;
    }

    public function writeConfig($destinationDir = null)
    {
        if (!$destinationDir) {
            $destinationDir = Platform::rootDir();
        }
        return file_put_contents($destinationDir . '/' . self::PLATFORM_CONFIG, Yaml::dump($this->config, 2));
    }
}
