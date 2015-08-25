<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 2:44 AM
 */

namespace Platformsh\Docker\Utils;


class DockerUtil
{
    public static function getContainerPort($type, $port, $protocol = 'tcp')
    {
        $containerName = self::getContainerName('nginx');
        $cmd = "docker inspect --format='{{(index (index .NetworkSettings.Ports \"{$port}/{$protocol}\") 0).HostPort}}' {$containerName}";
        return trim(shell_exec($cmd));
    }

    public static function getContainerName($type)
    {
        $projectName = str_replace('-', '', PlatformUtil::projectName());
        return $projectName . '_' . $type . '_1';
    }
}
