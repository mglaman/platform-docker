<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 11:50 PM
 */

namespace mglaman\PlatformDocker\Utils\Docker;


use mglaman\Docker\Docker;
use mglaman\PlatformDocker\Utils\Platform\Platform;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

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
          $this->projectPath . '/docker/conf/solr',
          $this->projectPath . '/docker/images',
          $this->projectPath . '/docker/ssl'
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

        // Change the default xdebug remote host on Mac, which uses a VM
        if (!Docker::native()) {
            $phpConfFile = $this->projectPath . '/docker/conf/php.ini';
            $phpConf = file_get_contents($phpConfFile);
            $phpConf = str_replace('172.17.42.1', '192.168.99.1', $phpConf);
            file_put_contents($phpConfFile, $phpConf);
        }

        // Quick fix to make nginx PHP_IDE_CONFIG dynamic for now.
        $nginxConfFile= $this->projectPath . '/docker/conf/nginx.conf';
        $nginxConf = file_get_contents($nginxConfFile);
        $nginxConf = str_replace('{{ platform }}', Platform::projectName() . '.' . Platform::projectTld(), $nginxConf);
        file_put_contents($nginxConfFile, $nginxConf);

        // stub in for Solr configs
        $finder = new Finder();
        $finder->in($this->resourcesDir . '/conf/solr')
               ->files()
               ->depth('< 1')
               ->name('*');
        /** @var \SplFileInfo $file */
        foreach ($finder as $file) {
            $this->fs->copy($file->getPathname(), $this->projectPath . '/docker/conf/solr/' . $file->getFilename());
        }

        // copy ssl
        $this->fs->copy($this->resourcesDir . '/ssl/nginx.crt', $this->projectPath . '/docker/ssl/nginx.crt');
        $this->fs->copy($this->resourcesDir . '/ssl/nginx.key', $this->projectPath . '/docker/ssl/nginx.key');
    }

    /**
     * @return array
     */
    protected function configsToCopy()
    {
        return ['fpm.conf', 'mysql.cnf', 'nginx.conf', 'php.ini',];
    }
}
