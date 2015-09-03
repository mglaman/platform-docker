<?php

namespace mglaman\PlatformDocker\Utils\Stacks;


use mglaman\Docker\Compose;
use Symfony\Component\Filesystem\Filesystem;
use mglaman\PlatformDocker\Utils\Platform\Platform;

/**
 * Class Settings
 * @package mglaman\PlatformDocker\Stacks\Drupal
 */
class Drupal implements StackTypeInterface
{
    /**
     * @var string
     */
    protected $string;
    /**
     * @var
     */
    protected $projectName;
    /**
     * @var string
     */
    protected $containerName;
    /**
     * Builds the settings.local.php file.
     */
    public function __construct()
    {
        $this->projectName = Platform::projectName();
        $this->containerName = Compose::getContainerName(Platform::projectName(), 'mariadb');

        $this->string = "<?php\n\n";
        $this->setSalt();
        $this->dbFromLocal();
        $this->dbFromDocker();
    }

    /**
     *
     */
    public function configure() {
        $fs = new Filesystem();

        if (!file_exists(Platform::webDir() . '/sites/default/settings.php')) {
            $fs->copy(CLI_ROOT . '/resources/stacks/drupal/drupal7.settings.php', Platform::webDir() . '/sites/default/settings.php', true);
        }
        $fs->dumpFile(Platform::sharedDir() . '/settings.local.php', $this->string);

        // Relink if missing.
        if (!$fs->exists(Platform::webDir() . '/sites/default/settings.local.php')) {
            $fs->symlink('../../../shared/settings.local.php', Platform::webDir() . '/sites/default/settings.local.php');
        }
    }

    public function setSalt()
    {
        $salt = hash('sha256', serialize($_SERVER));
        $this->string .= <<<EOT

/**
 * Salt for one-time login links and cancel links, form tokens, etc.
 *
 * If this variable is empty, a hash of the serialized database credentials
 * will be used as a fallback salt.
 */
\$drupal_hash_salt = '$salt';

EOT;
    }

    /**
     *
     */
    public function dbFromDocker() {
        $this->string .= <<<EOT
// Database configuration.
if (empty(\$_SERVER['PLATFORM_DOCKER'])) {
    \$cmd = "docker inspect --format='{{(index (index .NetworkSettings.Ports \"3306/tcp\") 0).HostPort}}' {$this->containerName}";
    \$port = trim(shell_exec(\$cmd));
    // Default config within Docker container.
    \$databases['default']['default'] = array(
      'driver' => 'mysql',
      'host' => '{$this->projectName}.platform',
      'port' => \$port,
      'username' => 'mysql',
      'password' => 'mysql',
      'database' => 'data',
      'prefix' => '',
    );
}
EOT;
    }

    /**
     *
     */
    public function dbFromLocal() {
        $this->string .= <<<EOT
// Database configuration.
\$databases['default']['default'] = array(
  'driver' => 'mysql',
  'host' => '{$this->containerName}',
  'username' => 'mysql',
  'password' => 'mysql',
  'database' => 'data',
  'prefix' => '',
);

EOT;
    }
}
