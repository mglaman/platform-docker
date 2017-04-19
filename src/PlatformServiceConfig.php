<?php

namespace mglaman\PlatformDocker;

/**
 * Reads the .platform/services.yaml configuration file.
 */
class PlatformServiceConfig
{
    use YamlConfigReader;

    /**
     * {@inheritdoc}
     */
    protected function getConfigFilePath()
    {
        return '.platform/services.yaml';
    }

    /**
     * Gets the solr type.
     *
     * @return string|FALSE
     *   FALSE if solr is not used.
     */
    public static function getSolrType() {
        $relationships = PlatformAppConfig::get('relationships');
        if (!isset($relationships['solr'])) {
            return FALSE;
        }
        list($solr_key, ) = explode(':', $relationships['solr']);
        $solr_config = self::get($solr_key);
        if (!isset($solr_config['type'])) {
            return FALSE;
        }
        return $solr_config['type'];
    }

    /**
     * Gets the solr major version
     *
     * @return string
     */
    public static function getSolrMajorVersion() {
        $type = static::getSolrType();
        if ($type) {
            list(, $version) = explode(":", $type);
            if (preg_match('/^(\d)\./', $version, $matches)) {
                return $matches[1];
            }
            return '4';
        }
    }

}
