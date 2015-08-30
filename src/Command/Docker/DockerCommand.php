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
        if (PHP_OS != 'Linux') {
            $this->validateNonLinux();
        } else {
            $this->validateLinux();
        }
    }

    protected function validateLinux()
    {
        // Give a Docker command a try.
        if (!Docker::dockerAvailable()) {
            $this->stdOut->writeln("<error>Unable to reach Docker service - try running `service docker start`</error>");
            exit(1);
        }
    }

    protected function validateNonLinux()
    {
        /** @var \Symfony\Component\Console\Helper\ProcessHelper $process */
        $process = $this->getHelper('process');
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
            $envOutput = $process->run($this->stdOut, 'docker-machine env ' . getenv('PLATFORM_DOCKER_MACHINE_NAME'));
            $envOutput = explode(PHP_EOL, $envOutput->getOutput());
            foreach ($envOutput as $line) {
                if (strpos($line, 'export') !== false) {
                    list($cmd, $export) = explode(' ', $line, 2);
                    list($key, $value) = explode('=', $export, 2);
                    $this->dockerParams[$key] = $value;

                    if ($this->stdOut->getVerbosity() > OutputInterface::VERBOSITY_VERBOSE) {
                        $this->stdOut->writeln("<info>$export</info>");
                    }
                }
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
        return $this->runDockerCommand('docker-compose', $command, $args);
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
        return $this->runDockerCommand('docker', $command, $args);
    }

    /**
     * @param $type
     * @param $command
     * @param $args
     *
     * @return \Symfony\Component\Process\Process
     */
    protected function runDockerCommand($type, $command, $args)
    {
        $this->prependCommand($type, $command, $args);

        $processBuilder = ProcessBuilder::create($args);
        $processBuilder->setTimeout(3600);

        if (PHP_OS != 'Linux' && !$this->envExported()) {
            foreach ($this->dockerParams as $key => $value) {
                $processBuilder->setEnv($key, $value);
            }
        }

        $process = $processBuilder->getProcess();

        $this->stdOut->writeln('<error>' . $process->getCommandLine() . '</error>');

        if ($this->stdOut->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
            $that = $this;
            $process->run(function ($type, $buffer) use ($process, $that) {
                if ($that->stdOut->getVerbosity() > OutputInterface::OUTPUT_NORMAL) {
                    if (Process::ERR == $type) {
                        $that->stdOut->writeln("<error>$buffer</error>");
                    } else {
                        $that->stdOut->write("<comment>$buffer</comment>");
                    }
                }
            });
        } else {
            $process->run(null);
        }

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process;
    }

    /**
     * @return bool
     */
    protected function envExported() {
        return (bool) !empty($this->dockerParams) || (getenv('DOCKER_MACHINE_NAME') || getenv('DOCKER_HOST') || getenv('DOCKER_CERT_PATH'));
    }

    protected function prependCommand($type, $command, &$args)
    {
        array_unshift($args, $command);
        array_unshift($args, $type);
    }
}
