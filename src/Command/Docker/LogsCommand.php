<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 12:13 AM
 */

namespace mglaman\PlatformDocker\Command\Docker;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ProcessBuilder;

class LogsCommand extends DockerCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
          ->setName('docker:logs')
          ->setDescription('Tails the logs of a specific service container')
          ->addArgument(
            'service',
            InputArgument::REQUIRED,
            'Service to SSH into the container of: http, php, db, redis, solr');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $containerName = null;
        $type = $input->getArgument('service');
        switch ($type) {
            case 'http':
                $this->executeDockerCompose('logs', ['nginx']);
                break;
            case 'php':
                $this->executeDockerCompose('logs', ['phpfpm']);
                break;
            case 'db':
                $this->executeDockerCompose('logs', ['mariadb']);
                break;
            case 'redis':
                $this->executeDockerCompose('logs', ['redis']);
                break;
            case 'solr':
                $this->stdOut->writeln("<error>Not provided yet</error>");
                break;
            default:
                $this->stdOut->writeln("<error>Invalid service type</error>");
                break;
        }
    }
}
