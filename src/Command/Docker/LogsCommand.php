<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 12:13 AM
 */

namespace mglaman\PlatformDocker\Command\Docker;

use mglaman\Docker\Compose;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
                Compose::logs(['nginx']);
                break;
            case 'php':
                Compose::logs(['phpfpm']);
                break;
            case 'db':
                Compose::logs(['mariadb']);
                break;
            case 'redis':
                Compose::logs(['redis']);
                break;
            case 'solr':
                Compose::logs(['solr']);
                break;
            default:
                $this->stdOut->writeln("<error>Invalid service type</error>");
                break;
        }
    }
}
