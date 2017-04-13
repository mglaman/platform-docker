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
        $this->addUnison();
        $this->addPhpFpm();
        $this->addDatabase();
        $this->addWebserver();
    }


    /**
     * @return string
     */
    public function yaml() {
        return Yaml::dump([
            'version' => '2',
            'services' => $this->config,
            'volumes' => ['code' => NULL],
        ], 10);
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
            './docker/conf/php.ini:/usr/local/etc/php/conf.d/local.ini',
            'code:/var/www/html',
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
            'MYSQL_USER' => 'mysql',
            'MYSQL_PASSWORD' => 'mysql',
            'MYSQL_ALLOW_EMPTY_PASSWORD' => 'yes',
            'MYSQL_ROOT_PASSWORD' => 'root,'
          ],
        ];
    }


    /**
     *
     */
    public function addUnison()
    {
        $this->config['unison'] = [
            'command' => '/usr/local/bin/unison /var/platform /var/www/html -confirmbigdel=false -prefer=/var/platform -fastcheck=true -times=true -watch=true -auto -batch -repeat=watch -retry=5 -ignore="Name {.git,*.swp,.idea,volumes,docker/*}"',
            'build'   => 'docker/images/unison',
            'volumes' => [
                './:/var/platform',
                'code:/var/www/html',
            ],
        ];
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
            'code:/var/www/html',
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
