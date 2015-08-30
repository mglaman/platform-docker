<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/30/15
 * Time: 5:34 AM
 */

namespace mglaman\PlatformDocker\Tests\Utils;

use mglaman\PlatformDocker\Utils\Platform\Config;
use mglaman\PlatformDocker\Utils\Platform\Platform;

class PlatformTest extends BaseUtilsTest
{
    public function testProjectName()
    {
        $this->assertEquals('phpunit', Platform::projectName());

        // Change values
        file_put_contents(Platform::rootDir() . '/' . Config::PLATFORM_CONFIG, '');
        Config::reset();
        $this->assertNull(Platform::projectName());
    }

    public function testGetRootDir()
    {
        $this->assertEquals(self::$tmpName, Platform::rootDir());
        mkdir(self::$tmpName . '/sub');
        mkdir(self::$tmpName . '/sub/sub');
        chdir(self::$tmpName . '/sub/sub');
        $this->assertEquals(self::$tmpName, Platform::rootDir());
    }

    public function testSharedDir()
    {
        mkdir(Platform::sharedDir());
        $this->assertTrue(is_dir(self::$tmpName . '/shared'));
    }

    public function testRepoDir()
    {
        mkdir(Platform::repoDir());
        $this->assertTrue(is_dir(self::$tmpName . '/repository'));
    }

    public function testWebRoot()
    {
        mkdir(Platform::webDir());
        $this->assertTrue(is_dir(self::$tmpName . '/www'));
    }

}
