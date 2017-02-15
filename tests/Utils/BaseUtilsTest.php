<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/30/15
 * Time: 5:39 AM
 */

namespace mglaman\PlatformDocker\Tests\Utils;

use mglaman\PlatformDocker\Config;

abstract class BaseUtilsTest extends \PHPUnit_Framework_TestCase
{
    protected static $tmpName;

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
