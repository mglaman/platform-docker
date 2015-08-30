<?php

namespace mglaman\PlatformDocker\Command\Docker;


use mglaman\PlatformDocker\Command\Command;
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

        /** @var \Symfony\Component\Console\Helper\ProcessHelper $process */
        $process = $this->getHelper('process');

        if (!$process->run($output, 'docker -v')->isSuccessful()) {
            $this->stdOut->writeln("<error>Cannot find docker command</error>");
        }
        if (!$process->run($output, 'docker-compose -v')->isSuccessful()) {
            $this->stdOut->writeln("<error>Cannot find docker-compose command</error>");
        }

        // For Mac OS X we need to ensure a Docker VM is running.
        if (PHP_OS != 'Linux') {
            // Check to see if Docker has been exported.
            if (!$this->envExported()) {
                $this->stdOut->writeln("<comment>Docker environment information not exported. Attempting from PLATFORM_DOCKER_MACHINE_NAME");
                if (getenv('PLATFORM_DOCKER_MACHINE_NAME')) {
                    // Attempt to boot the Docker VM
                    $process->mustRun($output, 'docker-machine start ' . getenv('PLATFORM_DOCKER_MACHINE_NAME'));
                    // Give it a chance to boot.
                    sleep(1);
                } else {
                    $this->stdOut->writeln("<error>You need to start your Docker machine and export the environment information");
                    exit(1);
                }

                // Export the Docker VM info on behalf of the user
                $envOutput = $process->run($output, 'docker-machine env ' . getenv('PLATFORM_DOCKER_MACHINE_NAME'));
                $envOutput = explode(PHP_EOL, $envOutput->getOutput());
                foreach ($envOutput as $line) {
                    if (strpos($line, 'export') !== false) {
                        list($cmd, $export) = explode(' ', $line);
                        $this->dockerParams[] = str_replace('"', '', $export);

                        if ($output->getVerbosity() > OutputInterface::VERBOSITY_VERBOSE) {
                            $this->stdOut->writeln("<info>$export</info>");
                        }
                    }
                }

                // Give a Docker command a try.
                if (!$this->executeDocker('ps')) {
                    $this->stdOut->writeln("<error>Unable to export Docker environment information</error>");
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
        return $this->runDockerCommand('docker-compose', $command, $args);
    }

    /**
     * @param $command
     * @param array $args
     *
     * @throws \Exception
     * @return bool
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
            foreach ($this->dockerParams as $param) {
                list($key, $value) = explode('=', $param, 2);
                $processBuilder->setEnv($key, $value);
            }
        }

        $process = $processBuilder->getProcess();

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
