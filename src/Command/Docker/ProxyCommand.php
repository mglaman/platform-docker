<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 12:13 AM
 */

namespace Platformsh\Docker\Command\Docker;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProxyCommand extends DockerCommand
{
    protected $containerName = 'nginx-proxy';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
          ->setName('docker:proxy')
          ->setAliases(['proxy'])
          ->addArgument(
            'operation',
            InputArgument::OPTIONAL,
            'Allows you to start or stop the nginx container proxy',
            'start'
          )
          ->setDescription('Starts the docker containers');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        switch($input->getArgument('operation')) {
            case 'stop':
                return $this->stopProxy();
            case 'start':
                return $this->startProxy();
            default:
                throw new \InvalidArgumentException('You must specify start or stop.');
        }
    }

    protected function stopProxy()
    {
        $this->stdOut->writeln("<comment>Stopping the nginx proxy container");
        return $this->executeDocker('stop', [$this->containerName]);
    }

    protected function startProxy()
    {
        $this->stdOut->writeln("<comment>Starting the nginx proxy container");
        if (!$this->executeDocker('start', [$this->containerName], false)) {
            // Container wasn't created yet.
            return $this->createProxy();
        }
        return 1;
    }

    protected function createProxy()
    {
        $this->stdOut->writeln("<comment>Creating the nginx proxy container");
        return $this->executeDocker('run', [
            '-d',
            '-p',
            '80:80',
            '-v',
            '/var/run/docker.sock:/tmp/docker.sock:ro',
            '--name',
            $this->containerName,
            'jwilder/nginx-proxy',
        ]);
    }
}
