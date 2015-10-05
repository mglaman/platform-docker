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

class WordPress extends StacksBase
{

    public function configure()
    {
        $this->fs->copy(CLI_ROOT . '/resources/stacks/wordpress/wp-config.php', Platform::webDir() . '/wp-config.php', true);
        $this->fs->remove(Platform::webDir() . '/wp-confg-sample.php');

        $this->fs->copy(CLI_ROOT . '/resources/stacks/wordpress/wp-config.local.php', Platform::sharedDir() . '/wp-config.local.php');

        $localSettings = file_get_contents(Platform::sharedDir() . '/wp-config.local.php');
        $localSettings = str_replace('{{ salts }}', $this->salts(), $localSettings);
        $localSettings = str_replace('{{ container_name }}', $this->containerName, $localSettings);
        $localSettings = str_replace('{{ project_domain }}', $this->projectName . '.' . $this->projectTld, $localSettings);
        file_put_contents(Platform::sharedDir() . '/wp-config.local.php', $localSettings);

        // Relink if missing.
        if (!$this->fs->exists(Platform::webDir() . '/wp-config.local.php')) {
            $this->fs->symlink('../shared/wp-config.local.php', Platform::webDir() . '/wp-config.local.php');
        }
    }

    /**
     * Returns salt defines.
     * @return string
     */
    public function salts() {
        return file_get_contents('https://api.wordpress.org/secret-key/1.1/salt/');
    }

}
