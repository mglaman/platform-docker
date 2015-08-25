<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 12:35 AM
 */

namespace Platformsh\Docker\Utils;


use Platformsh\Cli\Local\LocalProject;
use Symfony\Component\Filesystem\Filesystem;

class DrupalSettings
{
    protected $string;
    protected $projectName;
    protected $containerName;
    /**
     * Builds the settings.local.php file.
     */
    public function __construct()
    {
        $this->projectName = PlatformUtil::projectName();
        $this->containerName = DockerUtil::getContainerName('mariadb');

        $this->string = "<?php\n\n";
        $this->dbFromLocal();
        $this->dbFromDocker();
    }

    public function save() {
        $fs = new Filesystem();
        $fs->dumpFile(PlatformUtil::sharedDir() . '/settings.local.php', $this->string);
    }

    protected function dbFromDocker() {
        $this->string .= <<<EOT
// Database configuration.
if (empty(\$_SERVER['DOCKER'])) {
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
