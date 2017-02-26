<?php

namespace mglaman\PlatformDocker\Tests\Commands;

use mglaman\Docker\Docker;
use mglaman\PlatformDocker\Command\Docker\RebuildCommand;
use mglaman\PlatformDocker\Tests\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class RebuildCommandTest extends BaseTest
{
    protected static $dockerCleanup = TRUE;

    public function testRunCommand()
    {
        $tester = new CommandTester(new RebuildCommand());
        $tester->execute([], ['interactive' => true]);
        // Let all containers boot
        sleep(20);

        $dir_path = explode(DIRECTORY_SEPARATOR, self::$tmpName);
        $project_prefix = strtolower(end($dir_path));
        $process = Docker::inspect(['--format="{{ .State.Running }}"', $project_prefix . '_nginx_1'], true);
        $this->assertTrue(trim($process->getOutput()) == '"true"', $project_prefix . '_nginx_1 as not running');
        $process = Docker::inspect(['--format="{{ .State.Running }}"', $project_prefix . '_phpfpm_1'], true);
        $this->assertTrue(trim($process->getOutput()) == '"true"', $project_prefix . '_phpfpm_1 as not running');
        // MariaDB takes a little.
        $process = Docker::inspect(['--format="{{ .State.Running }}"', $project_prefix . '_mariadb_1'], true);
        $this->assertTrue(trim($process->getOutput()) == '"true"', $project_prefix . '_mariadb_1 as not running');
    }
}