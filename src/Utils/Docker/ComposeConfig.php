<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 11:50 PM
 */

namespace Platformsh\Docker\Utils\Docker;


use Platformsh\Cli\Local\LocalProject;
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
        $this->projectPath = LocalProject::getProjectRoot();
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
    }

    /**
     * @return array
     */
    protected function configsToCopy()
    {
        return ['fpm.conf', 'mysql.cnf', 'nginx.conf', 'php.ini'];
    }
}
