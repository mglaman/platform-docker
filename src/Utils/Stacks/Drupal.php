<?php

namespace mglaman\PlatformDocker\Utils\Stacks;


use mglaman\Docker\Compose;
use mglaman\Toolstack\Toolstack;
use mglaman\Toolstack\Stacks\Drupal as DrupalStackHelper;
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

    protected $projectTld;
    /**
     * @var string
     */
    protected $containerName;

    protected $version;
    /**
     * Builds the settings.local.php file.
     */
    public function __construct()
    {
        $this->projectName = Platform::projectName();
        $this->projectTld = Platform::projectTld();
        $this->containerName = Compose::getContainerName(Platform::projectName(), 'mariadb');

        /** @var DrupalStackHelper $drupalStack */
        $drupalStack = Toolstack::getStackByType('drupal');
        $this->version = $drupalStack->version(Platform::webDir());

        $this->string = "<?php\n\n";

        $this->setSalt();
        $this->dbFromLocal();
        $this->dbFromDocker();


        if ($this->version == DrupalStackHelper::DRUPAL8) {
            $this->string .= <<<EOT
// Configuration directories.
\$config_directories = array(
  CONFIG_ACTIVE_DIRECTORY => '../../../shared/config/active',
  CONFIG_STAGING_DIRECTORY => '../../../shared/config/staging',
);
EOT;
        }
    }

    /**
     *
     */
    public function configure() {
        $fs = new Filesystem();

        $fs->chmod(Platform::webDir() . '/sites/default', 0775);
        $fs->chmod(Platform::webDir() . '/sites/default/settings.php', 0664);

        if ($this->version  == DrupalStackHelper::DRUPAL7) {
            $fs->copy(CLI_ROOT . '/resources/stacks/drupal/drupal7.settings.php', Platform::webDir() . '/sites/default/settings.php', true);
        }
        elseif ($this->version  == DrupalStackHelper::DRUPAL8) {
            $fs->copy(CLI_ROOT . '/resources/stacks/drupal/drupal8.settings.php', Platform::webDir() . '/sites/default/settings.php', true);
        }

        if ($this->version == DrupalStackHelper::DRUPAL8) {
            $fs->mkdir([
                Platform::sharedDir() . '/config',
                Platform::sharedDir() . '/config/active',
                Platform::sharedDir() . '/config/staging',
            ]);
        }

        $fs->dumpFile(Platform::sharedDir() . '/settings.local.php', $this->string);

        // Relink if missing.
        if (!$fs->exists(Platform::webDir() . '/sites/default/settings.local.php')) {
            $fs->symlink('../../../shared/settings.local.php', Platform::webDir() . '/sites/default/settings.local.php');
        }

        $this->drushrc();
    }

    public function setSalt()
    {
        $salt = hash('sha256', serialize($_SERVER));
        if ($this->version == DrupalStackHelper::DRUPAL7) {
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
        elseif ($this->version == \mglaman\Toolstack\Stacks\Drupal::DRUPAL8) {
            $this->string .= <<<EOT
\$settings['hash_salt'] = '$salt';
EOT;
        }
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
      'host' => '{$this->projectName}.{$this->projectTld}',
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

    /**
     * Write a drushrc
     */
    public function drushrc() {

        $drushrc = <<<EOT
<?php
\$options['uri'] = "http://{$this->projectName}.{$this->projectTld}";
EOT;
        $fs = new Filesystem();
        $fs->dumpFile(Platform::sharedDir() . '/drushrc.php', $drushrc);
        $fs->symlink('../../../shared/drushrc.php', Platform::webDir() . '/sites/default/drushrc.php');
    }
}
