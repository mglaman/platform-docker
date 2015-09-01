<?php

namespace mglaman\PlatformDocker\Utils\Docker;

use mglaman\PlatformDocker\Utils\Platform\Platform;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Class Docker
 * @package mglaman\PlatformDocker\Utils\Docker
 */
class Docker
{
    /**
     * @param $type
     * @param $port
     * @param string $protocol
     *
     * @return string
     */
    public static function getContainerPort($type, $port, $protocol = 'tcp')
    {
        $containerName = self::getContainerName($type);
        $cmd = "docker inspect --format='{{(index (index .NetworkSettings.Ports \"{$port}/{$protocol}\") 0).HostPort}}' {$containerName}";
        return trim(shell_exec($cmd));
    }

    /**
     * @param $type
     *
     * @return string
     */
    public static function getContainerName($type)
    {
        $projectName = str_replace(array('-', '.'), '', Platform::projectName());
        return $projectName . '_' . $type . '_1';
    }

    /**
     * @return bool
     */
    public static function native() {
        return PHP_OS == 'Linux';
    }

    protected static function command($type, $command, $args = [], $callback = null)
    {
        // Place command before args
        array_unshift($args, $command);
        // Place docker/docker-compose/etc before command.
        array_unshift($args, $type);

        $processBuilder = ProcessBuilder::create($args);
        $processBuilder->setTimeout(3600);

        // Set environment variables. May have been defined with ::dockerMachineEnvironment
        // and not the parent process.
        if (!self::native()) {
            $processBuilder->setEnv('DOCKER_TLS_VERIFY', 1);
            $processBuilder->setEnv('DOCKER_MACHINE_NAME', getenv('DOCKER_MACHINE_NAME'));
            $processBuilder->setEnv('DOCKER_HOST', getenv('DOCKER_HOST'));
            $processBuilder->setEnv('DOCKER_CERT_PATH', getenv('DOCKER_CERT_PATH'));
        }

        $process = $processBuilder->getProcess();

        $process->run($callback);
        if (!$process->isSuccessful()) {
            throw new \Exception('Error executing docker command');
        }

        return $process;
    }

    /**
     * @return bool
     */
    public static function dockerExists()
    {
        return !(ProcessBuilder::create(['docker', '-v'])->getProcess()->run());
    }

    public static function dockerAvailable()
    {
        return self::dockerCommand('ps')->isSuccessful();
    }

    public static function dockerCommand($command, $args = [], $callback = null)
    {
        return self::command('docker', $command, $args, $callback);
    }

    public static function dockerComposeExists()
    {
        return !(ProcessBuilder::create(['docker-compose', '-v'])->getProcess()->run());
    }

    public static function dockerComposeCommand($command, $args = [], $callback = null)
    {
        return self::command('docker-compose', $command, $args, $callback);
    }

    public static function dockerMachineStart($name)
    {
        // Check if machine is running.
        $process = ProcessBuilder::create(['docker-machine', 'status', $name])->getProcess();
        $process->run();

        if (trim($process->getOutput()) == 'Stopped') {
            // If not, try to start it.
            return !(ProcessBuilder::create(['docker-machine', 'start', $name])->getProcess()->run());
        }

        return (trim($process->getOutput()) == trim('Running'));
    }

    public static function dockerMachineEnvironment($name)
    {
        $envs = [];
        $output = ProcessBuilder::create(['docker-machine', 'env', $name])->getProcess();
        $output->run();
        if (trim($output->getOutput()) == "$name is not running. Please start this with docker-machine start $name") {
            throw new \Exception('Docker machine has not been started yet..');
        }
        $envOutput = explode(PHP_EOL, $output->getOutput());
        foreach ($envOutput as $line) {
            if (strpos($line, 'export') !== false) {
                list($cmd, $export) = explode(' ', $line, 2);
                list($key, $value) = explode('=', $export, 2);
                $envs[$key] = str_replace('"', '', $value);
            }
        }
        return $envs;
    }

    public static function dockerMachineExported()
    {
        return self::native() || ((getenv('DOCKER_MACHINE_NAME') && getenv('DOCKER_HOST') && getenv('DOCKER_CERT_PATH')));
    }
}
