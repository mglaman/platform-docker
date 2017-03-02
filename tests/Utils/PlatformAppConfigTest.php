<?php

namespace mglaman\PlatformDocker\Tests\Utils;

use mglaman\PlatformDocker\PlatformAppConfig;

class PlatformAppConfigTest extends BaseUtilsTest
{


    public function testGetPhpVersion()
    {
        $config = new PlatformAppConfig();
        $this->assertEquals('7.0', $config->getPhpVersion());
    }

}
