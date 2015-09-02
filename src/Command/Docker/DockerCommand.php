<?php

namespace mglaman\PlatformDocker\Command\Docker;


use mglaman\Docker\Compose;
use mglaman\PlatformDocker\Command\Command;
use mglaman\Docker\Docker;
use mglaman\Docker\Machine;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Class DockerCommand
 * @package mglaman\PlatformDocker\Command\Docker
 */
abstract class DockerCommand extends Command
{
    protected $dockerParams;

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \Exception
     */
    protected function initialize(InputInterface $input, OutputInterface $output) {
        parent::initialize($input, $output);

        if (!Docker::exists()) {
            $this->stdOut->writeln("<error>Cannot find docker command</error>");
            exit(1);
        }
        if (!Compose::exists()) {
            $this->stdOut->writeln("<error>Cannot find docker-compose command</error>");
            exit(1);
        }

        // For Mac OS X we need to ensure a Docker VM is running.
        if (!Docker::native()) {
            $this->validateNonNative();
        } else {
            $this->validateNative();
        }
    }

    protected function validateNative()
    {
        // Give a Docker command a try.
        if (!Docker::available()) {
            $this->stdOut->writeln("<error>Unable to reach Docker service - try running `service docker start`</error>");
            exit(1);
        }
    }

    protected function validateNonNative()
    {
        // Check to see if Docker has been exported.
        if (!$this->envExported()) {
            $this->stdOut->writeln("<comment>Docker environment information not exported. Attempting from PLATFORM_DOCKER_MACHINE_NAME");
            if (getenv('PLATFORM_DOCKER_MACHINE_NAME')) {
                // Attempt to boot the Docker VM
                if (!Machine::start(getenv('PLATFORM_DOCKER_MACHINE_NAME'))) {
                    $this->stdOut->writeln("<error>Failed to start Docker machine</error>");
                    exit(1);
                }
            } else {
                $this->stdOut->writeln("<error>You need to start your Docker machine and export the environment information");
                exit(1);
            }

            // Export the Docker VM info on behalf of the user
            $this->dockerParams = Machine::getEnv(getenv('PLATFORM_DOCKER_MACHINE_NAME'));
            foreach ($this->dockerParams as $key => $value) {
                putenv("$key=$value");
            }
        }
        // Give a Docker command a try.
        if (!Docker::available()) {
            $this->stdOut->writeln("<error>Unable to reach Docker service - try manually exporting environment variables.</error>");
            exit(1);
        }
    }

    /**
     * @return bool
     */
    protected function envExported() {
        return (bool) !empty($this->dockerParams) || Machine::isExported();
    }
}
