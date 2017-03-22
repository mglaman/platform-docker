<?php

namespace mglaman\PlatformDocker\Tests\Utils;

use mglaman\PlatformDocker\PlatformAppConfig;

class PlatformAppConfigTest extends BaseUtilsTest
{

    public function setUp()
    {
        // Don't do set up.
    }

    /**
     * @dataProvider providerGetPhpVersion
     */
    public function testGetPhpVersion($fixture, $expected)
    {
        $this->createTestProject($fixture);
        $config = new PlatformAppConfig();
        $this->assertEquals($expected, $config->getPhpVersion());
    }

    public function providerGetPhpVersion() {
        return [
            ['.platform.app.yaml', '7.0'],
            ['5.6.platform.app.yaml', '5.6'],
        ];
    }

    /**
     * @dataProvider providerHasService
     */
    public function testHasRedis($fixture, $expected)
    {
        $this->createTestProject($fixture);
        $this->assertEquals($expected, PlatformAppConfig::hasSolr());
    }

    /**
     * @dataProvider providerHasService
     */
    public function testHasSolr($fixture, $expected)
    {
        $this->createTestProject($fixture);
        $this->assertEquals($expected, PlatformAppConfig::hasSolr());
    }

    public function providerHasService() {
        return [
            ['.platform.app.yaml', TRUE],
            ['5.6.platform.app.yaml', FALSE],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        exec('rm -Rf ' . escapeshellarg(self::$tmpName));
    }

}
