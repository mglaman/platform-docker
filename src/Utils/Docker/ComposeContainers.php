<?php

namespace mglaman\PlatformDocker\Utils\Docker;


use mglaman\PlatformDocker\Utils\Platform\Platform;
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
}
