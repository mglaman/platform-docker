<?php

namespace mglaman\PlatformDocker\Tests\Utils;


use mglaman\PlatformDocker\Docker\ComposeContainers;
use mglaman\PlatformDocker\Platform;
use mglaman\PlatformDocker\Tests\BaseTest;
use Symfony\Component\Yaml\Yaml;

class ComposeContainersTest extends BaseTest
{
    public function testDefaultConfig()
    {
        $config = new ComposeContainers(Platform::rootDir(), Platform::projectName());
        $config_converted = Yaml::parse($config->yaml());

        $this->assertArrayHasKey('phpfpm', $config_converted);
        $this->assertArrayHasKey('nginx', $config_converted);
        $this->assertArrayHasKey('mariadb', $config_converted);
        $this->assertCount(3, $config_converted);
    }

    public function testAddExtras()
    {
        $config = new ComposeContainers(Platform::rootDir(), Platform::projectName());
        $config->addRedis();
        $config_converted = Yaml::parse($config->yaml());
        $this->assertCount(4, $config_converted);
        $config->addSolr();
        $config_converted = Yaml::parse($config->yaml());
        $this->assertCount(5, $config_converted);
    }

}
