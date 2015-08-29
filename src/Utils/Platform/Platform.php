<?php

namespace mglaman\PlatformDocker\Utils\Platform;


use Platformsh\Cli\Local\LocalProject;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Platform
 * @package mglaman\PlatformDocker\Utils\Platform
 */
class Platform
{
    public static function getConfig($key = null)
    {
        $platformConfig = LocalProject::getProjectConfig();
        if ($key) {
            return (isset($platformConfig[$key]) ? $platformConfig[$key] : null);
        }
        return $platformConfig;
    }
    /**
     * @return mixed
     */
    public static function projectName()
    {
        $platformConfig = self::getConfig();
        foreach (['name', 'alias-group', 'id'] as $key) {
            if (isset($platformConfig[$key])) {
                return $platformConfig[$key];
            }
        }
    }

    public static function rootDir()
    {
        $dir = self::getConfig('path');
        if ($dir) {
            return $dir;
        }
        return LocalProject::getProjectRoot();
    }

    /**
     * @return string
     */
    public static function sharedDir()
    {
        return LocalProject::getProjectRoot() . '/' . LocalProject::SHARED_DIR;
    }

    /**
     * @return string
     */
    public static function repoDir()
    {
        return LocalProject::getProjectRoot() . '/' . LocalProject::REPOSITORY_DIR;
    }

    public static function webDir()
    {
        return LocalProject::getProjectRoot() . '/' . LocalProject::WEB_ROOT;
    }

    /**
     * @return array
     */
    public static function projectServices()
    {
        $services = self::repoDir() . '/.platform/services.yaml';
        if (file_exists($services)) {
            return Yaml::parse(file_get_contents($services));
        }
        return null;
    }
}
