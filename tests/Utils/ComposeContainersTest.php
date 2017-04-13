<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/30/15
 * Time: 7:31 AM
 */

namespace mglaman\PlatformDocker\Tests\Utils;


use mglaman\PlatformDocker\Docker\ComposeContainers;
use mglaman\PlatformDocker\Platform;
use Symfony\Component\Yaml\Yaml;

class ComposeContainersTest extends BaseUtilsTest
{
    public function testDefaultConfig()
    {
        $config = new ComposeContainers(Platform::rootDir(), Platform::projectName());
        $config_converted = Yaml::parse($config->yaml())['services'];

        $this->assertArrayHasKey('phpfpm', $config_converted);
        $this->assertArrayHasKey('nginx', $config_converted);
        $this->assertArrayHasKey('mariadb', $config_converted);
        $this->assertArrayHasKey('unison', $config_converted);
        $this->assertCount(4, $config_converted);
    }

    public function testAddExtras()
    {
        $config = new ComposeContainers(Platform::rootDir(), Platform::projectName());
        $config->addRedis();
        $config_converted = Yaml::parse($config->yaml());
        $this->assertCount(5, $config_converted['services']);
        $config->addSolr();
        $config_converted = Yaml::parse($config->yaml());
        $this->assertCount(6, $config_converted['services']);
    }

}
