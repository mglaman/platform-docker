<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 1:38 AM
 */

namespace Platformsh\Docker\Command\Docker;


use Platformsh\Docker\Command\Command;

abstract class DockerCommand extends Command
{
    protected function executeDockerCompose($command, array $args = []) {
        $shell = $this->getHelper('shell');

        array_unshift($args, $command);
        array_unshift($args, 'docker-compose');

        $shell->execute($args, null, true, false);
    }
}
