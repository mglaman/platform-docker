<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 11:50 PM
 */

namespace mglaman\PlatformDocker\Utils\Docker;


use mglaman\PlatformDocker\Utils\Platform\Platform;
use Symfony\Component\Filesystem\Filesystem;

class ComposeConfig
{
    /**
     * @var string
     */
    protected $resourcesDir;

    /**
     * @var Filesystem
     */
    protected $fs;

    protected $projectPath;

    /**
     *
     */
    public function __construct()
    {
        $this->resourcesDir = CLI_ROOT . '/resources';
        $this->projectPath = Platform::rootDir();
        $this->fs = new Filesystem();
    }

    public function writeDockerCompose(ComposeContainers $composeContainers)
    {
        $this->fs->dumpFile($this->projectPath . '/docker-compose.yml', $composeContainers->yaml());
    }

    /**
     *
     */
    public function ensureDirectories()
    {
        $this->fs->mkdir([
          $this->projectPath . '/xhprof',
          $this->projectPath . '/docker/data',
          $this->projectPath . '/docker/conf',
          $this->projectPath . '/docker/images/php'
        ]);
    }

    public function copyImages()
    {
        // Copy Dockerfile for php-fpm
        $this->fs->copy($this->resourcesDir . '/images/php/Dockerfile',
          $this->projectPath . '/docker/images/php/Dockerfile');
    }

    public function copyConfigs()
    {
        // Copy configs
        foreach ($this->configsToCopy() as $fileName) {
            $this->fs->copy($this->resourcesDir . '/conf/' . $fileName,
              $this->projectPath . '/docker/conf/' . $fileName);
        }

        // Quick fix to make nginx PHP_IDE_CONFIG dynamic for now.
        $nginxConfFile= $this->projectPath . '/docker/conf/nginx.conf';
        $nginxConf = file_get_contents($nginxConfFile);
        $nginxConf = str_replace('{{ platform }}', Platform::projectName() . '.platform', $nginxConf);
        file_put_contents($nginxConfFile, $nginxConf);
    }

    /**
     * @return array
     */
    protected function configsToCopy()
    {
        return ['fpm.conf', 'mysql.cnf', 'nginx.conf', 'php.ini'];
    }
}
