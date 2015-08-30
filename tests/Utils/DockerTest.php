<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/30/15
 * Time: 7:56 AM
 */

namespace mglaman\PlatformDocker\Tests\Utils;


use mglaman\PlatformDocker\Utils\Docker\Docker;

class DockerTest extends \PHPUnit_Framework_TestCase
{
    function testDockerExists()
    {
        $this->assertTrue(Docker::dockerExists());
    }
    function testDockerAvailable()
    {
        $this->assertTrue(Docker::dockerAvailable());
    }
    function testDockerComposeExists()
    {
        $this->assertTrue(Docker::dockerComposeExists());
    }
}
