<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 12:24 AM
 */

namespace Platformsh\Docker\Utils;


use Symfony\Component\Yaml\Yaml;
use Platformsh\Cli\Local\LocalProject;

class DockerComposeConfig
{
    protected $config;
    protected $path;
    protected $name;

    /**
     * Builds Docker Compose YML file.
     *
     * @param $name
     */
    function __construct($name)
    {
        $this->path = LocalProject::getProjectRoot();
        $this->name = $name;
        // Add required containers.
        $this->addPhpFpm();
        $this->addDatabase();
        $this->addWebserver();
    }


    public function yaml() {
        return Yaml::dump($this->config);
    }

    public function addPhpFpm()
    {
        $this->config['phpfpm'] = [
          'command' => 'php-fpm --allow-to-run-as-root',
          'build'   => 'docker/images/php',
          'volumes' => [
            $this->path . '/docker/conf/fpm.conf:/usr/local/etc/php-fpm.conf',
            $this->path . ':/var/platform',
            $this->path . '/docker/conf/php.ini:/usr/local/etc/php/conf.d/local.ini',
          ],
          'links' => [
            'mariadb',
            'redis',
          ],
          'environment' => [
            'PLATFORM_DOCKER' => $this->name,
          ],
        ];
    }

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
            $this->path . '/docker/data:/var/lib/mysql',
            $this->path . '/docker/conf/mysql.cnf:/etc/mysql/my.cnf',
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

    public function addWebserver()
    {
        $this->config['nginx'] = [
          'image' => 'nginx:1.9.0',
          'volumes' => [
            $this->path . '/docker/conf/nginx.conf:/etc/nginx/conf.d/default.conf',
            $this->path . ':/var/platform',
          ],
          'ports' => [
            '80',
            '443',
          ],
          'links' => [
            'phpfpm',
          ],
          'environment' => [
            'VIRTUAL_HOST' => $this->name . '.platform',
            'PLATFORM_DOCKER' => $this->name,
          ],
        ];
    }

    public function addRedis() {
        $this->config['redis'] = [
          'image' => 'redis',
        ];
    }
}
