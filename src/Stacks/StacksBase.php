<?php

namespace mglaman\PlatformDocker\Stacks;

use mglaman\Docker\Compose;
use mglaman\PlatformDocker\Platform;
use mglaman\PlatformDocker\PlatformAppConfig;
use Symfony\Component\Filesystem\Filesystem;

abstract class StacksBase  implements StackTypeInterface {
    /**
     * @var
     */
    protected $projectName;

    protected $projectTld;

    /**
     * @var string
     */
    protected $containerName;

    /**
     * @var string
     */
    protected $redisContainerName;

    protected $version;

    protected $fs;

    /**
     * Initiates basic variables.
     */
    public function __construct()
    {
        $this->fs            = new Filesystem();
        $this->projectName   = Platform::projectName();
        $this->projectTld    = Platform::projectTld();
        $this->containerName = Compose::getContainerName(Platform::projectName(), 'mariadb');
        $this->redisContainerName = PlatformAppConfig::hasRedis() ? Compose::getContainerName(Platform::projectName(), 'redis') : '';
    }
}
