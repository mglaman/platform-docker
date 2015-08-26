<?php

namespace Platformsh\Docker\Command\Docker;


use Platformsh\Cli\Helper\ShellHelper;
use Platformsh\Docker\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DockerCommand
 * @package Platformsh\Docker\Command\Docker
 */
abstract class DockerCommand extends Command
{
    /**
     * @var
     */
    protected $docker;

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \Exception
     */
    protected function initialize(InputInterface $input, OutputInterface $output) {
        parent::initialize($input, $output);

        /** @var ShellHelper $shell */
        $shell = $this->getHelper('shell');

        if (!$shell->commandExists('docker')) {
            $this->stdOut->writeln("<error>Cannot find docker command</error>");
        }
        if (!$shell->commandExists('docker-compose')) {
            $this->stdOut->writeln("<error>Cannot find docker command</error>");
        }

        // For Mac OS X we need to ensure a Docker VM is running.
        if (PHP_OS == 'Darwin') {
            // Check to see if Docker has been exported.
            if (!$this->envExported()) {
                $this->stdOut->writeln("<comment>Docker environment information not exported. Attempting from PLATFORM_DOCKER_MACHINE_NAME");
                if (getenv('PLATFORM_DOCKER_MACHINE_NAME')) {
                    // Attempt to boot the Docker VM
                    $shell->execute([
                      'docker-machine',
                      'start',
                      getenv('PLATFORM_DOCKER_MACHINE_NAME'),
                    ], null, true);
                    // Give it a chance to boot.
                    sleep(2);
                    // Export the Docker VM info on behalf of the user
                    $shell->execute([
                        'eval "$(docker-machine env ' . getenv('PLATFORM_DOCKER_MACHINE_NAME') . ')"',
                    ], null, true);
                    // Give a Docker command a try.
                    $shell->execute(['docker', 'ps']);
                } else {
                    $this->stdOut->writeln("<error>You need to start your Docker machine and export the environment information");
                    exit(1);
                }
            }
        }

    }


    /**
     * @param $command
     * @param array $args
     */
    protected function executeDockerCompose($command, array $args = [])
    {
        $shell = $this->getHelper('shell');

        array_unshift($args, $command);
        array_unshift($args, 'docker-compose');

        $shell->execute($args, null, true, false);
    }

    /**
     * @param $command
     * @param array $args
     *
     * @throws \Exception
     */
    protected function executeDocker($command, array $args = [])
    {
        /** @var ShellHelper $shell */
        $shell = $this->getHelper('shell');
        array_unshift($args, $command);
        array_unshift($args, 'docker');
        $shell->execute($args, null, true, true);
    }

    /**
     * @return bool
     */
    protected function envExported() {
        return (bool) (getenv('DOCKER_MACHINE_NAME') || getenv('DOCKER_HOST') || getenv('DOCKER_CERT_PATH'));
    }
}
