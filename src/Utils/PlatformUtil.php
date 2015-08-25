<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 1:52 AM
 */

namespace Platformsh\Docker\Utils;


use Platformsh\Cli\Local\LocalProject;
use Symfony\Component\Yaml\Yaml;

class PlatformUtil
{
    public static function projectName()
    {
        $platformConfig = LocalProject::getProjectConfig();
        if (isset($platformConfig['alias-group'])) {
            return $platformConfig['alias-group'];
        } else {
            return $platformConfig['id'];
        }
    }

    public static function sharedDir()
    {
        return LocalProject::getProjectRoot() . '/' . LocalProject::SHARED_DIR;
    }

    public static function repoDir()
    {
        return LocalProject::getProjectRoot() . '/' . LocalProject::REPOSITORY_DIR;
    }

    public static function projectServices()
    {
        $services = self::repoDir() . '/.platform/services.yaml';
        if (file_exists($services)) {
            return Yaml::parse(file_get_contents($services));
        }
    }
}
