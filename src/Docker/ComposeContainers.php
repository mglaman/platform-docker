<?php

namespace mglaman\PlatformDocker\Docker;


use mglaman\PlatformDocker\Config;
use mglaman\PlatformDocker\Platform;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ComposeConfig
 * @package mglaman\PlatformDocker\Utils\Docker
 */
class ComposeContainers
{
    /**
     * @var
     */
    protected $config;
    /**
     * @var false|string
     */
    protected $path;
    /**
     * @var
     */
    protected $name;

    /**
     * Builds Docker Compose YML file.
     *
     * @param $path
     * @param $name
     */
    function __construct($path, $name)
    {
        $this->path = $path;
        $this->name = $name;
        // Add required containers.
        $this->addPhpFpm();
        $this->addDatabase();
        $this->addWebserver();
    }


    /**
     * @return string
     */
    public function yaml() {
        return Yaml::dump($this->config);
    }

    /**
     *
     */
    public function addPhpFpm()
    {
        $this->config['phpfpm'] = [
          'command' => 'php-fpm --allow-to-run-as-root',
          'build'   => 'docker/images/php',
          'volumes' => [
            './docker/conf/fpm.conf:/usr/local/etc/php-fpm.conf',
            './:/var/platform',
            './docker/conf/php.ini:/usr/local/etc/php/conf.d/local.ini',
          ],
          'links' => [
            'mariadb',
          ],
          'environment' => [
            'PLATFORM_DOCKER' => $this->name,
            'PHP_IDE_CONFIG' => 'serverName=' . $this->name . '.' . Platform::projectTld(),
          ],
        ];
    }

    /**
     *
     */
    public function addDatabase()
    {
        $this->config['mariadb'] = [
            // @todo if comman run with verbose, tag verbose.
          'command' => 'mysqld --user=root --verbose',
          'image' => 'mariadb',
          'ports' => [
            '3306',
          ],
          'volumes' => [
            './docker/data:/var/lib/mysql',
            './docker/conf/mysql.cnf:/etc/mysql/my.cnf',
          ],
          'environment' => [
            'MYSQL_DATABASE' => 'data',
            'MYSQL_USER' => static::getMysqlUser(),
            'MYSQL_PASSWORD' => static::getMysqlPassword(),
            'MYSQL_ALLOW_EMPTY_PASSWORD' => 'yes',
            'MYSQL_ROOT_PASSWORD' => 'root,'
          ],
        ];
    }

    public static function getMysqlUser() {
        $my_cnf = [];
        $my_cnf_file = static::getUserDirectory() . '/.my.cnf';
        if (file_exists($my_cnf_file)) {
            $my_cnf = parse_ini_file($my_cnf_file);
        }
        return isset($my_cnf['user']) ? $my_cnf['user'] : 'mysql';
    }

    public static function getMysqlPassword() {
        $my_cnf = [];
        $my_cnf_file = static::getUserDirectory() . '/.my.cnf';
        if (file_exists($my_cnf_file)) {
            $my_cnf = parse_ini_file($my_cnf_file);
        }
        return isset($my_cnf['password']) ? $my_cnf['password'] : 'mysql';
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

    /**
     *
     */
    public function addWebserver()
    {
        $this->config['nginx'] = [
          'image' => 'nginx:1.9.0',
          'volumes' => [
            './docker/conf/nginx.conf:/etc/nginx/conf.d/default.conf',
            './:/var/platform',
            './docker/ssl/nginx.crt:/etc/nginx/ssl/nginx.crt',
            './docker/ssl/nginx.key:/etc/nginx/ssl/nginx.key',
          ],
          'ports' => [
            '80',
            '443',
          ],
          'links' => [
            'phpfpm',
          ],
          'environment' => [
            'VIRTUAL_HOST' => $this->name . '.' . Platform::projectTld(),
            'PLATFORM_DOCKER' => $this->name,
          ],
        ];
    }

    /**
     *
     */
    public function addRedis() {
        $this->config['redis'] = [
            'image' => 'redis',
            'ports' => [
                '6379',
            ],
        ];
        $this->config['phpfpm']['links'][] = 'redis';
    }

    public function addSolr()
    {
        $this->config['solr'] = [
          'image'   => 'makuk66/docker-solr:4.10.4',
          'ports' => [
              '8893',
          ],
          'volumes' => [
            './docker/conf/solr:/opt/solr/example/solr/collection1/conf',
          ],
        ];
        $this->config['phpfpm']['links'][] = 'solr';
        $this->config['nginx']['links'][] = 'solr';
    }

    public function addMemcached() {
        $this->config['memcached'] = [
          'image' => 'memcached',
        ];
        $this->config['phpfpm']['links'][] = 'memcached';
    }

    public function addBlackfire() {
        $this->config['blackfire'] = [
            'image' => 'blackfire/blackfire',
            'ports' => [
                '8707',
            ],
            'environment' => [
                'BLACKFIRE_SERVER_ID' => Config::get('blackfire_server_id'),
                'BLACKFIRE_SERVER_TOKEN' => Config::get('blackfire_server_token'),
                'BLACKFIRE_LOG_LEVEL' => 4
            ],
        ];
        $this->config['phpfpm']['links'][] = 'blackfire';
    }
}
