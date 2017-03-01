<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/29/15
 * Time: 10:01 PM
 */

namespace mglaman\PlatformDocker;

use Symfony\Component\Yaml\Yaml;

trait YamlConfigReader
{

    /**
     * @var object
     *   The instance of the class so we can use static getters.
     */
    protected static $instance;

    /**
     * The configuration read from YAML.
     *
     * @var array
     */
    protected $config = array();

    /**
     * @param bool $refresh
     *   If true, create an new instance. If false, use the existing instance if it exists.
     *
     * @return object
     */
    protected static function instance($refresh = false)
    {
        if (self::$instance === null || $refresh === true) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * A constructor for the config reader.
     */
    public function __construct()
    {
        $path = Platform::rootDir() . '/' . $this->getConfigFilePath();
        if ((empty($this->config)) && file_exists($path)) {
            $this->config = Yaml::parse(file_get_contents($path));
        }
    }

    /**
     * The path to the configuration to read. Relative to the Platform root dir.
     *
     * @return string
     */
    abstract protected function getConfigFilePath();

    /**
     * Gets a value from the configuration for the specified key.
     *
     * @param string|null $key
     *
     * @return mixed
     */
    public static function get($key = null)
    {
        return self::instance()->getConfig($key);
    }

    /**
     * Gets a value from the configuration for the specified key.
     *
     * @param string|null $key
     *
     * @return mixed
     */
    public function getConfig($key = null)
    {
        if ($key) {
            return isset($this->config[$key]) ? $this->config[$key] : null;
        }
        return $this->config;
    }

    /**
     * Resets the static instance.
     *
     * @return object
     */
    public static function reset()
    {
        return self::instance(true);
    }

}
