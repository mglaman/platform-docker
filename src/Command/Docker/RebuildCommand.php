<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 12:13 AM
 */

namespace Platformsh\Docker\Command\Docker;

use Platformsh\Docker\Utils\Docker\ComposeConfig;
use Platformsh\Docker\Utils\Docker\ComposeContainers;
use Platformsh\Docker\Utils\Drupal;
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
        $this->stdOut->writeln('<info>Building containers</info>');

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

        $composeContainers = new ComposeContainers($this->projectName);
        // @todo check if services.yml has redis
        $composeContainers->addRedis();
        $composeConfig->writeDockerCompose($composeContainers);

        // @todo: see if this is a drupal project
        $drupalSettings = new Drupal\Settings();
        $drupalSettings->save();

        $this->executeDockerCompose('build');
        $this->executeDockerCompose('up', ['-d']);
    }
}
