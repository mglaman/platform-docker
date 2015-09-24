<?php

namespace mglaman\PlatformDocker\Utils\Platform;


/**
 * Class Platform
 * @package mglaman\PlatformDocker\Utils\Platform
 */
class Platform
{
    const REPOSITORY_DIR = 'repository';
    const SHARED_DIR = 'shared';
    const WEB_ROOT = 'www';
    const TEST_ROOT = 'tests';

    /**
     * @return mixed
     */
    public static function projectName()
    {
        $platformConfig = Config::get();
        foreach (['name', 'alias-group', 'id'] as $key) {
            if (isset($platformConfig[$key])) {
                return $platformConfig[$key];
            }
        }
        return null;
    }

    public static function rootDir()
    {
        static $rootDir, $lastDir;
        $cwd = getcwd();

        if ($rootDir !== null && $lastDir === $cwd) {
            return $rootDir;
        }

        $rootDir = null;
        $lastDir = $current = $cwd;
        do {
            if (file_exists($current . '/' . Config::PLATFORM_CONFIG)) {
                $rootDir = $current;
                break;
            }

            $up = dirname($current);
            if ($up == $current || $up == '.') {
                break;
            }
            $current = $up;
        } while(!$rootDir);

        return $rootDir;
    }

    /**
     * @return string
     */
    public static function sharedDir()
    {
        return self::rootDir() . '/' . self::SHARED_DIR;
    }

    /**
     * @return string
     */
    public static function repoDir()
    {
        return self::rootDir() . '/' . self::REPOSITORY_DIR;
    }

    public static function webDir()
    {
        return self::rootDir() . '/' . self::WEB_ROOT;
    }

    public static function testsDir()
    {
        return self::rootDir() . '/' . self::TEST_ROOT;
    }
}
