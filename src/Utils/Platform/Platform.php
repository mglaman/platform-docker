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
    /**
     * @return mixed
     */
    public static function projectName()
    {
        $platformConfig = LocalProject::getProjectConfig();
        if (isset($platformConfig['alias-group'])) {
            return $platformConfig['alias-group'];
        } else {
            return $platformConfig['id'];
        }
    }

    public static function rootDir()
    {
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
