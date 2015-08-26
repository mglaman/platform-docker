<?php

namespace Platformsh\Docker\Command\Docker;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Class InitCommand
 * @package Platformsh\Docker\Command\Docker
 */
class InitCommand extends DockerCommand
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
            $this->stdOut->writeln("<info>Docker compose initiated, starting containers. Run docker:rebuild to rebuild.");
            return $this->getApplication()->find('docker:up')->run($input, $output);
        }

        $this->getApplication()->find('docker:rebuild')->run($input, $output);
        sleep(5);
        $this->getApplication()->find('platform:db-sync')->run($input, $output);
    }


}
