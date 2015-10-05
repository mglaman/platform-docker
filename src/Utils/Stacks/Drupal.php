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
class Drupal extends StacksBase
{
    /**
     * Builds the settings.local.php file.
     */
    public function __construct()
    {
        parent::__construct();

        /** @var DrupalStackHelper $drupalStack */
        $drupalStack = Toolstack::getStackByType('drupal');
        $this->version = $drupalStack->version(Platform::webDir());
    }

    /**
     *
     */
    public function configure() {
        $this->ensureSettings();
        $this->ensureLocalSettings();
        $this->drushrc();
    }

    /**
     * Ensures that the Drupal settings.php is available.
     * @throws \Exception
     */
    protected function ensureSettings() {
        // If building from an existing project, Drupal may have fiddled with
        // the permissions preventing us from writing.
        $this->fs->chmod(Platform::webDir() . '/sites/default', 0775);
        if ($this->fs->exists(Platform::webDir() . '/sites/default/settings.php')) {
            $this->fs->chmod(Platform::webDir() . '/sites/default/settings.php', 0664);
        }

        switch ($this->version) {
            case DrupalStackHelper::DRUPAL7:
                $this->fs->copy(CLI_ROOT . '/resources/stacks/drupal7/settings.php', Platform::webDir() . '/sites/default/settings.php', true);
                break;
            case DrupalStackHelper::DRUPAL8:
                $this->fs->copy(CLI_ROOT . '/resources/stacks/drupal8/settings.php', Platform::webDir() . '/sites/default/settings.php', true);
                $this->fs->mkdir([
                  Platform::sharedDir() . '/config',
                  Platform::sharedDir() . '/config/active',
                  Platform::sharedDir() . '/config/staging',
                ]);
                break;
            default:
                throw new \Exception('Unsupported version of Drupal. Write a pull reuqest!');
        }
    }

    protected function ensureLocalSettings() {
        // @todo: Check if settings.local.php exists, load in any $conf changes.
        switch ($this->version) {
            case DrupalStackHelper::DRUPAL7:
                $this->fs->copy(CLI_ROOT . '/resources/stacks/drupal7/settings.local.php', Platform::sharedDir() . '/settings.local.php', true);
                break;
            case DrupalStackHelper::DRUPAL8:
                $this->fs->copy(CLI_ROOT . '/resources/stacks/drupal8/settings.local.php', Platform::sharedDir() . '/settings.local.php', true);
                break;
            default:
                throw new \Exception('Unsupported version of Drupal. Write a pull reuqest!');
        }

        // Replace template variables.
        $localSettings = file_get_contents(Platform::sharedDir() . '/settings.local.php');
        $localSettings = str_replace('{{ salt }}', hash('sha256', serialize($_SERVER)), $localSettings);
        $localSettings = str_replace('{{ container_name }}', $this->containerName, $localSettings);
        $localSettings = str_replace('{{ project_domain }}', $this->projectName . '.' . $this->projectTld, $localSettings);
        file_put_contents(Platform::sharedDir() . '/settings.local.php', $localSettings);

        // Relink if missing.
        if (!$this->fs->exists(Platform::webDir() . '/sites/default/settings.local.php')) {
            $this->fs->symlink('../../../shared/settings.local.php', Platform::webDir() . '/sites/default/settings.local.php');
        }
    }


    /**
     * Write a drushrc
     */
    public function drushrc() {
        // @todo: Check if drushrc.php exists, load in any $conf changes.
        switch ($this->version) {
            case DrupalStackHelper::DRUPAL7:
                $this->fs->copy(CLI_ROOT . '/resources/stacks/drupal7/drushrc.php', Platform::sharedDir() . '/drushrc.php', true);
                break;
            case DrupalStackHelper::DRUPAL8:
                $this->fs->copy(CLI_ROOT . '/resources/stacks/drupal8/drushrc.php', Platform::sharedDir() . '/drushrc.php', true);
                break;
            default:
                throw new \Exception('Unsupported version of Drupal. Write a pull reuqest!');
        }

        // Replace template variables.
        $localSettings = file_get_contents(Platform::sharedDir() . '/drushrc.php');
        // @todo this expects proxy to be running.
        $localSettings = str_replace('{{ project_domain }}', $this->projectName . '.' . $this->projectTld, $localSettings);
        file_put_contents(Platform::sharedDir() . '/drushrc.php', $localSettings);

        $this->fs->symlink('../../../shared/drushrc.php', Platform::webDir() . '/sites/default/drushrc.php');
    }
}
