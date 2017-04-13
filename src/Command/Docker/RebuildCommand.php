<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 12:13 AM
 */

namespace mglaman\PlatformDocker\Command\Docker;

use mglaman\Docker\Compose;
use mglaman\Docker\Docker;
use mglaman\PlatformDocker\Config;
use mglaman\PlatformDocker\Platform;
use mglaman\PlatformDocker\PlatformAppConfig;
use mglaman\PlatformDocker\Stacks\StacksFactory;
use mglaman\Toolstack\Toolstack;
use mglaman\Toolstack\Stacks;
use mglaman\PlatformDocker\Docker\ComposeConfig;
use mglaman\PlatformDocker\Docker\ComposeContainers;
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
          ->setAliases(['rebuild'])
          ->setDescription('Rebuild configurations and containers');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $platform_config = new PlatformAppConfig();
        $composeConfig = new ComposeConfig($platform_config->getPhpVersion());

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
                    case 'memcached':
                        $composeContainers->addMemcached();
                        break;
                    case 'blackfire':
                        $composeContainers->addBlackfire();
                        break;
                }
            }
        }
        // Support services defined in .platform/services.yaml
        else {
            if (PlatformAppConfig::hasRedis()) {
                $composeContainers->addRedis();
            }
        }

        $composeConfig->writeDockerCompose($composeContainers);

        $stack = Toolstack::inspect(Platform::webDir());
        if ($stack) {
            $this->stdOut->writeln("<comment>Configuring stack:</comment> " . $stack->type());
            StacksFactory::configure($stack->type());
        }

        // Stop and remove any existing containers.
        Compose::stop();
        Compose::rm(TRUE, TRUE);

        $this->stdOut->writeln('<info>Building the containers</info>');
        Compose::build();

        $this->stdOut->writeln('<info>Bringing up the containers</info>');
        Compose::up(['-d']);
        $uri = Platform::getUri();
        $name = Platform::projectName();
        $this->stdOut->writeln("<info>$name available at $uri</info>");
    }
}
