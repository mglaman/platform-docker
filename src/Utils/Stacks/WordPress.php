<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/28/15
 * Time: 2:56 PM
 */

namespace mglaman\PlatformDocker\Utils\Stacks;


use mglaman\Docker\Compose;
use mglaman\PlatformDocker\Utils\Docker\Docker;
use mglaman\PlatformDocker\Utils\Platform\Platform;
use Symfony\Component\Filesystem\Filesystem;

class WordPress implements StackTypeInterface
{
    /**
     * @var string
     */
    protected $string;

    public function __construct()
    {
        $this->string = <<<EOT
<?php

define('DB_NAME', 'data');
define('DB_USER', 'mysql');
define('DB_PASSWORD', 'mysql');
EOT;
        $this->dbFromLocal();
        $this->dbFromDocker();
        $this->salts();
        $this->debug();
    }

    public function configure()
    {
        $fs = new Filesystem();
        $fs->copy(CLI_ROOT . '/resources/stacks/wordpress/wp-config.php', Platform::webDir() . '/wp-config.php', true);
        $fs->remove(Platform::webDir() . '/wp-confg-sample.php');

        $fs->dumpFile(Platform::sharedDir() . '/wp-config.local.php', $this->string);

        // Relink if missing.
        if (!$fs->exists(Platform::webDir() . '/wp-config.local.php')) {
            $fs->symlink('../shared/wp-config.local.php', Platform::webDir() . '/wp-config  .local.php');
        }
    }

    public function dbFromDocker()
    {
        $hostname = Compose::getContainerName(Platform::projectName(), 'mariadb');
        $this->string .= <<<EOT
if (!empty(\$_SERVER['PLATFORM_DOCKER'])) {
    define('DB_HOST', '{$hostname}');
}
EOT;
    }

    public function dbFromLocal()
    {
        $hostname = Compose::getContainerName(Platform::projectName(), 'mariadb');
        $name = Platform::projectName();
        $this->string .= <<<EOT
if (empty(\$_SERVER['PLATFORM_DOCKER'])) {
    \$cmd = "docker inspect --format='{{(index (index .NetworkSettings.Ports \"3306/tcp\") 0).HostPort}}' {$hostname}";
    \$port = trim(shell_exec(\$cmd));
    define('DB_HOST', "{$name}.platform:\$port");
}
EOT;
    }


    /**
     * Returns salt defines.
     * @return string
     */
    public function salts() {
        return file_get_contents('https://api.wordpress.org/secret-key/1.1/salt/');
    }

    public function debug()
    {
        $this->string .= <<<EOT
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', true);
define('SAVEQUERIES', true);
EOT;

    }

}
