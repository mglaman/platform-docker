<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/24/15
 * Time: 10:40 PM
 */

namespace Platformsh\Docker\Command\Docker;

use Platformsh\Docker\Command\Command;
use Platformsh\Docker\Utils\DockerComposeConfig;
use Platformsh\Docker\Utils\DrupalSettings;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class InitCommand extends Command
{
    /**
     * @var Filesystem;
     */
    protected $fs;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
          ->setName('docker:init')
          ->setDescription('Setup the Platform.sh Docker Compose files');
    }

    /**
     * @inheritdoc
     */
    protected function initialize(InputInterface $input, OutputInterface $output) {
        parent::initialize($input, $output);
        $this->fs = new Filesystem();
    }


    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // If the docker-compose.yml file exists, then start containers.
        if ($this->fs->exists($this->projectPath . '/docker-compose.yml')) {
            $this->stdOut->writeln("<info>Docker compose initiated, starting containers");
            return $this->getApplication()->find('docker:up')->run($input, $output);
        }
        // Else build the resources.
        $this->stdOut->writeln('<info>Building containers</info>');

        $resourcesDir = CLI_ROOT . '/resources';

        // Create docker folder in project.
        try {
            $this->fs->mkdir([
              $this->projectPath . '/docker/data',
              $this->projectPath . '/docker/conf',
              $this->projectPath . '/docker/images/php'
            ]);
        } catch (IOException $e) {
            $this->stdOut->writeln("<error>Error while trying to create docker-compose directories.</error>");
        }

        // Copy Dockerfile for php-fpm
        $this->fs->copy($resourcesDir . '/images/php/Dockerfile',
          $this->projectPath . '/docker/images/php/Dockerfile');

        // Copy configs
        foreach ($this->configsToCopy() as $fileName) {
            $this->fs->copy($resourcesDir . '/conf/' . $fileName,
              $this->projectPath . '/docker/conf/' . $fileName);
        }

        $dockerCompose = new DockerComposeConfig($this->projectName);
        // @todo check if services.yml has redis
        $dockerCompose->addRedis();
        $this->fs->dumpFile($this->projectPath . '/docker-compose.yml', $dockerCompose->yaml());

        // @todo: see if this is a drupal project
        $drupalHelper = new DrupalSettings();
        $drupalHelper->save();

        $this->stdOut->writeln("<info>Bring up containers</info>");
        $this->getApplication()->find('docker:up')->run($input, $output);
        $this->stdOut->writeln("<info>Syncing Platform.sh environment database to local</info>");
        $this->getApplication()->find('platform:db-sync')->run($input, $output);

        return 1;
    }

    protected function configsToCopy()
    {
        return ['fpm.conf', 'mysql.cnf', 'nginx.conf', 'php.ini'];
    }
}
