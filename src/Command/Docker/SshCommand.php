<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 12:13 AM
 */

namespace mglaman\PlatformDocker\Command\Docker;

use mglaman\Docker\Compose;
use mglaman\PlatformDocker\Utils\Docker\Docker;
use mglaman\PlatformDocker\Utils\Platform\Platform;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ProcessBuilder;

class SshCommand extends DockerCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
          ->setName('docker:ssh')
          ->setDescription('Allows for quick SSH into a service container.')
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
                $containerName = Compose::getContainerName(Platform::projectName(), 'nginx');
                break;
            case 'php':
                $containerName = Compose::getContainerName(Platform::projectName(), 'phpfpm');
                break;
            case 'db':
                $containerName = Compose::getContainerName(Platform::projectName(), 'mariadb');
                break;
            case 'redis':
                $containerName = Compose::getContainerName(Platform::projectName(), 'redis');
                break;
            case 'solr':
                $containerName = Compose::getContainerName(Platform::projectName(), 'solr');
                break;
            default:
                $this->stdOut->writeln("<error>Invalid service type</error>");
                break;
        }

        $builder = ProcessBuilder::create([
          'docker',
          'exec',
          '-it',
          $containerName,
          'bash'
        ]);
        $process = $builder->getProcess();
        // Need to set tty true, ProccessHelper doesn't allow this setting.
        $process->setTty(true);
        try {
            $process->mustRun(null);
        } catch(ProcessFailedException $e) {
            $message = "The command failed with the exit code: " . $process->getExitCode();
            $message .= "\n\nFull command: " . $process->getCommandLine();
            throw new \Exception($message);
        }
    }
}
