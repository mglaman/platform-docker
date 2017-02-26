<?php

namespace mglaman\PlatformDocker\Tests;

use mglaman\PlatformDocker\Config;

abstract class BaseTest extends \PHPUnit_Framework_TestCase
{
    protected static $tmpName;
    protected static $dockerCleanup = FALSE;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     */
    protected function setUp()
    {
        $this->createTestProject();
    }


    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass()
    {
        if (self::$dockerCleanup) {
            $dir_path = explode(DIRECTORY_SEPARATOR, self::$tmpName);
            $project_prefix = strtolower(end($dir_path));
            exec('docker stop $(docker ps -q -f name=' . $project_prefix . ')');
            exec('docker rm $(docker ps -q -f name=' . $project_prefix . ')');
        }
        exec('rm -Rf ' . escapeshellarg(self::$tmpName));
    }

    protected function createTestProject()
    {
        $testDir = self::$tmpName = tempnam(sys_get_temp_dir(), '');
        unlink($testDir);
        mkdir($testDir);

        file_put_contents($testDir . '/' . Config::PLATFORM_CONFIG, 'name: phpunit');
        chdir($testDir);
    }
}
