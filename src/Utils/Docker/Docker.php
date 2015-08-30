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
    public static function dockerExists()
    {
        return !(ProcessBuilder::create(['docker'])->getProcess()->run());
    }

    public static function dockerAvailable()
    {
        return !(ProcessBuilder::create(['docker', 'ps'])->getProcess()->run());
    }

    public static function dockerComposeExists()
    {
        return !(ProcessBuilder::create(['docker-compose', '-v'])->getProcess()->run());
    }

    public static function dockerMachineStart($name)
    {
        // Check if machine is running.
        if (ProcessBuilder::create(['docker-machine', 'inspect', $name])->getProcess()->run() !== 0) {
            // If not, try to start it.
            return !(ProcessBuilder::create(['docker-machine', 'start', $name])->getProcess()->run());
        }

        return true;
    }
}
