<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 12:13 AM
 */

namespace mglaman\PlatformDocker\Command\Docker;

use mglaman\Docker\Compose;
use mglaman\PlatformDocker\Utils\Platform\Config;
use mglaman\PlatformDocker\Utils\Platform\Platform;
use mglaman\PlatformDocker\Utils\Stacks\WordPress;
use mglaman\Toolstack\Toolstack;
use mglaman\Toolstack\Stacks;
use mglaman\PlatformDocker\Utils\Docker\ComposeConfig;
use mglaman\PlatformDocker\Utils\Docker\ComposeContainers;
use mglaman\PlatformDocker\Utils\Stacks\Drupal;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;

class RebuildCommand extends DockerCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
          ->setName('docker:rebuild')
          ->setDescription('Rebuild configurations and containers');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $composeConfig = new ComposeConfig();

        // Create docker folder in project.
        try {
            $composeConfig->ensureDirectories();
        } catch (IOException $e) {
            $this->stdOut->writeln("<error>Error while trying to create docker-compose directories.</error>");
            exit(1);
        }

        $composeConfig->copyImages();
        $composeConfig->copyConfigs();

        $composeContainers = new ComposeContainers(Platform::rootDir(), Config::get('name'));

        // @todo: With #20 and making tool provider aware, read those configs. Or push those configs to main.
        if (isset(Config::get()['services'])) {
            foreach(Config::get('services') as $service) {
                switch($service) {
                    case 'redis':
                        $composeContainers->addRedis();
                        break;
                    case 'solr':
                        $composeContainers->addSolr();
                        break;
                }
            }
        }

        $composeConfig->writeDockerCompose($composeContainers);

        // @todo move this into a static class to run configure based on type.
        $stack = Toolstack::inspect(Platform::webDir());
        if ($stack) {
            $this->stdOut->writeln("<comment>Configuring stack:</comment> " . $stack->type());
            switch ($stack->type()) {
                case Stacks\Drupal::TYPE:
                    $drupal = new Drupal();
                    $drupal->configure();
                    break;
                case Stacks\WordPress::TYPE:
                    $wordpress = new WordPress();
                    $wordpress->configure();
                    break;
            }
        }

        $this->stdOut->writeln('<info>Building the containers</info>');
        Compose::build();

        $this->stdOut->writeln('<info>Bringing up the containers</info>');
        Compose::up(['-d']);
    }
}
