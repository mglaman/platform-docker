<?php

namespace mglaman\PlatformDocker\Tests\Utils;

use mglaman\PlatformDocker\PlatformServiceConfig;

/**
 * @coversDefaultClass \mglaman\PlatformDocker\PlatformServiceConfig
 */
class PlatformAppConfigTest extends BaseUtilsTest
{

    /**
     * @covers ::getSolrType
     */
    public function testGetSolrType()
    {
        $this->createTestProject();
        $this->assertEquals('solr:6.3', PlatformServiceConfig::getSolrType());
    }

    /**
     * @covers ::getSolrMajorVersion
     */
    public function testGetSolrMajorVersion()
    {
        $this->createTestProject();
        $this->assertEquals('6', PlatformServiceConfig::getSolrMajorVersion());
    }

}
