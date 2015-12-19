<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/30/15
 * Time: 7:56 AM
 */

namespace mglaman\PlatformDocker\Tests\Utils;


use mglaman\Docker\Docker;
use mglaman\Docker\Compose;

class DockerTest extends \PHPUnit_Framework_TestCase
{
    function testDockerExists()
    {
        $this->assertTrue(Docker::exists());
    }
    function testDockerAvailable()
    {
        $this->assertTrue(Docker::available());
    }
    function testDockerComposeExists()
    {
        $this->assertTrue(Compose::exists());
    }
}
