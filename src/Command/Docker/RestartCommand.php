<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 12:13 AM
 */

namespace mglaman\PlatformDocker\Command\Docker;

use mglaman\Docker\Compose;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RestartCommand extends DockerCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
          ->setName('docker:restart')
          ->setAliases(['reboot'])
          ->setDescription('Restarts the docker containers');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->stdOut->writeln("<info>Restarting containers</info>");
        Compose::restart();
    }
}
