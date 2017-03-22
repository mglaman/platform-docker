<?php

namespace mglaman\PlatformDocker;

/**
 * Reads the .platform.app.yaml configuration file.
 */
class PlatformAppConfig
{
    use YamlConfigReader;

    /**
     * {@inheritdoc}
     */
    protected function getConfigFilePath()
    {
        return '.platform.app.yaml';
    }

    /**
     * @return string|null
     *   The PHP version string from the platform configuration file. NULL if it can not be determined.
     */
    public function getPhpVersion() {
        list($app, $version) = explode(':', $this->get('type'), 2);
        if (strcasecmp($app, 'php') === 0) {
            return $version;
        }
        return NULL;
    }

    /**
     * Determines if the site is using redis.
     *
     * @return bool
     */
    public static function hasRedis() {
        $relationships = self::get('relationships');
        return isset($relationships['redis']);
    }

    /**
     * Determines if the site is using solr.
     *
     * @return bool
     */
    public static function hasSolr() {
        $relationships = self::get('relationships');
        return isset($relationships['solr']);
    }

}
