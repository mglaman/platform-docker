<?php

namespace Platformsh\Docker\Utils\Docker;

use Platformsh\Docker\Utils\Platform\Platform;

/**
 * Class Docker
 * @package Platformsh\Docker\Utils\Docker
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
        $projectName = str_replace('-', '', Platform::projectName());
        return $projectName . '_' . $type . '_1';
    }
}
