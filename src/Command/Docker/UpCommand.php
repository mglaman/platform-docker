<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 12:13 AM
 */

namespace Platformsh\Docker\Command\Docker;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpCommand extends DockerCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
          ->setName('docker:up')
          ->setAliases(['start', 'up'])
          ->setDescription('Starts the docker containers');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->executeDockerCompose('up', ['-d']);
    }
}
