<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 12:13 AM
 */

namespace mglaman\PlatformDocker\Command\Docker;

use mglaman\PlatformDocker\Utils\Docker\Docker;
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
                $containerName = Docker::getContainerName('nginx');
                break;
            case 'php':
                $containerName = Docker::getContainerName('phpfpm');
                break;
            case 'db':
                $containerName = Docker::getContainerName('mariadb');
                break;
            case 'redis':
                $containerName = Docker::getContainerName('redis');
                break;
            case 'solr':
                $this->stdOut->writeln("<error>Not provided yet</error>");
                break;
            default:
                $this->stdOut->writeln("<error>Invalid service type</error>");
                break;
        }

        $builder = new ProcessBuilder([
          'docker',
          'exec',
          '-it',
          $containerName,
          'bash'
        ]);
        $process = $builder->getProcess();
        // Need to set tty true, ShellHelper doesn't allow this setting.
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
