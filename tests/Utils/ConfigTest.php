<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/30/15
 * Time: 7:12 AM
 */

namespace mglaman\PlatformDocker\Tests\Utils;


use mglaman\PlatformDocker\Config;
use mglaman\PlatformDocker\Tests\BaseTest;

class ConfigTest extends BaseTest
{

    public function testGet()
    {
        $this->assertArrayHasKey('name', Config::get());
        $this->assertEquals('phpunit', Config::get('name'));
    }

    public function testSet()
    {
        Config::set('stack', 'wordpress');
        $this->assertEquals('wordpress', Config::get('stack'));
        Config::reset();
        $this->assertArrayNotHasKey('stack', Config::get());

        Config::set('stack', 'wordpress');
        Config::write();
        Config::reset();
        $this->assertEquals('wordpress', Config::get('stack'));
    }
}
