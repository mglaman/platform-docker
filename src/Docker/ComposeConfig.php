<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 11:50 PM
 */

namespace mglaman\PlatformDocker\Docker;


use mglaman\Docker\Docker;
use mglaman\PlatformDocker\Config;
use mglaman\PlatformDocker\Platform;
use mglaman\PlatformDocker\PlatformServiceConfig;
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
     * The PHP Version in the format MAJOR.MINOR.
     *
     * @var string
     */
    protected $phpVersion;

    /**
     * ComposeConfig constructor.
     *
     * @param string $php_version
     *   The PHP Version in the format MAJOR.MINOR, for example '7.0'.
     */
    public function __construct($phpVersion = '5.6')
    {
        $this->resourcesDir = CLI_ROOT . '/resources';
        $this->projectPath = Platform::rootDir();
        $this->fs = new Filesystem();
        $this->phpVersion = $phpVersion ?: '5.6';
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
        $this->fs->copy($this->resourcesDir . "/images/php/{$this->phpVersion}/Dockerfile",
          $this->projectPath . '/docker/images/php/Dockerfile', TRUE);
    }

    public function copyConfigs()
    {
        // Copy configs
        foreach ($this->configsToCopy() as $source => $fileName) {
            $this->fs->copy($this->resourcesDir . '/conf/' . $source,
              $this->projectPath . '/docker/conf/' . $fileName, TRUE);
        }

        // Change the default xdebug remote host when using Docker Machine
        if (!Docker::native()) {
            $phpConfFile = $this->projectPath . '/docker/conf/php.ini';
            $phpConf = file_get_contents($phpConfFile);
            $phpConf = str_replace('172.17.42.1', '192.168.99.1', $phpConf);
            file_put_contents($phpConfFile, $phpConf);
        }
        // Change xdebug remote host for Windows and Mac beta
        // @todo No idea if this IP matches on Windows.
        elseif (PHP_OS != 'Linux') {
          $phpConfFile = $this->projectPath . '/docker/conf/php.ini';
          $phpConf = file_get_contents($phpConfFile);
          $phpConf = str_replace('172.17.42.1', '192.168.65.1', $phpConf);
          file_put_contents($phpConfFile, $phpConf);
        }

        // Quick fix to make nginx PHP_IDE_CONFIG dynamic for now.
        $nginxConfFile= $this->projectPath . '/docker/conf/nginx.conf';
        $nginxConf = file_get_contents($nginxConfFile);
        $nginxConf = str_replace('{{ platform }}', Platform::projectName() . '.' . Platform::projectTld(), $nginxConf);
        $nginxConf = str_replace('{{ docroot }}', Config::get('docroot'), $nginxConf);
        file_put_contents($nginxConfFile, $nginxConf);

        // stub in for Solr configs
        $solr_major_version = PlatformServiceConfig::getSolrMajorVersion();
        $finder = new Finder();
        $finder->in($this->resourcesDir . "/conf/solr/$solr_major_version.x/")
               ->files()
               ->depth('< 1')
               ->name('*');
        /** @var \SplFileInfo $file */
        foreach ($finder as $file) {
            $this->fs->copy($file->getPathname(), $this->projectPath . '/docker/conf/solr/' . $file->getFilename(), TRUE);
        }

        // copy ssl
        $this->fs->copy($this->resourcesDir . '/ssl/nginx.crt', $this->projectPath . '/docker/ssl/nginx.crt', TRUE);
        $this->fs->copy($this->resourcesDir . '/ssl/nginx.key', $this->projectPath . '/docker/ssl/nginx.key', TRUE);
    }

    /**
     * @return array
     */
    protected function configsToCopy()
    {
        return [
            "php/{$this->phpVersion}/fpm.conf" => 'fpm.conf',
            'mysql.cnf' => 'mysql.cnf',
            'nginx.conf' => 'nginx.conf',
            "php/{$this->phpVersion}/php.ini" => 'php.ini',
        ];
    }
}
