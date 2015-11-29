<?php

namespace mglaman\PlatformDocker\Stacks;

use mglaman\Docker\Compose;
use mglaman\PlatformDocker\Platform;
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
    }
}
