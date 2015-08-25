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
        $projectName = PlatformUtil::projectName();
        $cmd = "docker inspect --format='{{(index (index .NetworkSettings.Ports \"{$port}/{$protocol}\") 0).HostPort}}' {$projectName}_{$type}_1";
        return trim(shell_exec($cmd));
    }
}
