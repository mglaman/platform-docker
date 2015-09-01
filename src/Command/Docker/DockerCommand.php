<?php

namespace mglaman\PlatformDocker\Command\Docker;


use mglaman\PlatformDocker\Command\Command;
use mglaman\PlatformDocker\Utils\Docker\Docker;
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

        if (!Docker::dockerExists()) {
            $this->stdOut->writeln("<error>Cannot find docker command</error>");
            exit(1);
        }
        if (!Docker::dockerComposeExists()) {
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
        if (!Docker::dockerAvailable()) {
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
                if (!Docker::dockerMachineStart(getenv('PLATFORM_DOCKER_MACHINE_NAME'))) {
                    $this->stdOut->writeln("<error>Failed to start Docker machine</error>");
                    exit(1);
                }
            } else {
                $this->stdOut->writeln("<error>You need to start your Docker machine and export the environment information");
                exit(1);
            }

            // Export the Docker VM info on behalf of the user
            $this->dockerParams = Docker::dockerMachineEnvironment(getenv('PLATFORM_DOCKER_MACHINE_NAME'));
            foreach ($this->dockerParams as $key => $value) {
                putenv("$key=$value");
            }
        }
        // Give a Docker command a try.
        if (!Docker::dockerAvailable()) {
            $this->stdOut->writeln("<error>Unable to reach Docker service - try manually exporting environment variables.</error>");
            exit(1);
        }
    }


    /**
     * @param $command
     * @param array $args
     * @return Process
     */
    protected function executeDockerCompose($command, array $args = [])
    {
        $that = $this;
        return Docker::dockerComposeCommand($command, $args, function ($type, $buffer) use ($that) {
            if ($that->stdOut->getVerbosity() > OutputInterface::OUTPUT_NORMAL) {
                if (Process::ERR == $type) {
                    $that->stdOut->writeln("<error>$buffer</error>");
                } else {
                    $that->stdOut->write("<comment>$buffer</comment>");
                }
            }
        });
    }

    /**
     * @param $command
     * @param array $args
     *
     * @throws \Exception
     * @return Process
     */
    protected function executeDocker($command, array $args = [])
    {
        $that = $this;
        return Docker::dockerCommand($command, $args, function ($type, $buffer) use ($that) {
            if ($that->stdOut->getVerbosity() > OutputInterface::OUTPUT_NORMAL) {
                if (Process::ERR == $type) {
                    $that->stdOut->writeln("<error>$buffer</error>");
                } else {
                    $that->stdOut->write("<comment>$buffer</comment>");
                }
            }
        });
    }

    /**
     * @return bool
     */
    protected function envExported() {
        return (bool) !empty($this->dockerParams) || Docker::dockerMachineExported();
    }
}
