<?php

namespace mglaman\PlatformDocker\Mysql;

/**
 * Static methods to get info about the local mysql environment.
 *
 * We need to read .my.cnf files if they exist and use the user information from it. This is because of a bug in the
 * most recent versions of Drush that is unlikely to be ported to Drush 8 or 7.
 *
 * @see https://github.com/drush-ops/drush/pull/2387
 */
class Mysql
{

    /**
     * The mysql user to use.
     *
     * @return string
     */
    public static function getMysqlUser() {
        $my_cnf = [];
        $my_cnf_file = static::getUserDirectory() . '/.my.cnf';
        if (file_exists($my_cnf_file)) {
            $my_cnf = parse_ini_file($my_cnf_file);
        }
        return isset($my_cnf['user']) ? $my_cnf['user'] : 'mysql';
    }

    /**
     * The mysql password to use.
     *
     * @return string
     */
    public static function getMysqlPassword() {
        $my_cnf = [];
        $my_cnf_file = static::getUserDirectory() . '/.my.cnf';
        if (file_exists($my_cnf_file)) {
            $my_cnf = parse_ini_file($my_cnf_file);
        }
        return isset($my_cnf['password']) ? $my_cnf['password'] : 'mysql';
    }

    /**
     * The mysql root password to use.
     *
     * @return string
     */
    public static function getMysqlRootPassword() {
        $password = 'root,';
        $my_cnf_file = static::getUserDirectory() . '/.my.cnf';
        if (file_exists($my_cnf_file)) {
            $my_cnf = parse_ini_file($my_cnf_file);
            if (isset($my_cnf['password'], $my_cnf['user']) && $my_cnf['user'] === 'root') {
                $password = $my_cnf['password'];
            }
        }
        return $password;
    }

    /**
     * @return string The formal user home as detected from environment parameters
     * @throws \RuntimeException If the user home could not reliably be determined
     */
    public static function getUserDirectory()
    {
        if (false !== ($home = getenv('HOME'))) {
            return $home;
        }
        if (defined('PHP_WINDOWS_VERSION_BUILD') && false !== ($home = getenv('USERPROFILE'))) {
            return $home;
        }
        if (function_exists('posix_getuid') && function_exists('posix_getpwuid')) {
            $info = posix_getpwuid(posix_getuid());
            return $info['dir'];
        }
        throw new \RuntimeException('Could not determine user directory');
    }
}
