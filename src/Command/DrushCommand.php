<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 2:51 AM
 */

namespace mglaman\PlatformDocker\Command;

use mglaman\Docker\Compose;
use mglaman\PlatformDocker\Command\Docker\DockerCommand;
use mglaman\PlatformDocker\Utils\Platform\Platform;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use mglaman\Docker\Docker;
use Symfony\Component\Process\ProcessBuilder;

class DrushCommand extends DockerCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
          ->setName('drush')
          ->addArgument('cmd', InputArgument::OPTIONAL, 'Command and arguments to pass to Drush', 'status')
          ->setDescription('Runs a Drush command for environment.');

    }

    /**
     * {@inheritdoc}
     *
     * @see PlatformCommand::getCurrentEnvironment()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $processBuilder = ProcessBuilder::create([
            'drush',
            $input->getArgument('cmd'),
            '--root=' . Platform::webDir(),
            '--uri=' . Platform::projectName() . '.' . Platform::projectTld()
        ]);
        passthru($processBuilder->getProcess()->getCommandLine());
    }
}
