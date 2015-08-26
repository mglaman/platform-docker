<?php

namespace Platformsh\Docker\Utils\Drupal;


use Symfony\Component\Filesystem\Filesystem;
use Platformsh\Docker\Utils\Platform\Platform;
use Platformsh\Docker\Utils\Docker\Docker;

/**
 * Class Settings
 * @package Platformsh\Docker\Utils\Drupal
 */
class Settings
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
        $this->containerName = Docker::getContainerName('mariadb');

        $this->string = "<?php\n\n";
        $this->dbFromLocal();
        $this->dbFromDocker();
    }

    /**
     *
     */
    public function save() {
        $fs = new Filesystem();
        $fs->dumpFile(Platform::sharedDir() . '/settings.local.php', $this->string);

        // Relink if missing.
        if (!$fs->exists(Platform::webDir() . '/sites/default/settings.local.php')) {
            $fs->symlink('../../../shared/settings.local.php', Platform::webDir() . '/sites/default/settings.local.php');
        }
    }

    /**
     *
     */
    protected function dbFromDocker() {
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
    protected function dbFromLocal() {
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
