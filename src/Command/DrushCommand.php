<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 2:51 AM
 */

namespace mglaman\PlatformDocker\Command;

use mglaman\PlatformDocker\Command\Docker\DockerCommand;
use mglaman\PlatformDocker\Platform;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
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
            ->addArgument('cmd', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Command and arguments to pass to Drush', ['status'])
            ->setDescription('Runs a Drush command for environment.')
            ->setHelp('For example, <info>drush en --drush_option=-y contact</info>')
            ->addOption('drush_option', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Additional options to pass to Drush. For example --drush_option=-y');

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
            '--root=' . Platform::webDir(),
            '--uri=' . Platform::getUri()
        ]);
        foreach ($input->getArgument('cmd') as $argument) {
            $processBuilder->add($argument);
        }
        foreach ($input->getOption('drush_option') as $option) {
            $processBuilder->add($option);
        }

        if ($output->getVerbosity() > $output::VERBOSITY_NORMAL) {
            $output->writeln('Running drush command: <info>' . $processBuilder->getProcess()->getCommandLine() . '</info>');
        }

        passthru($processBuilder->getProcess()->getCommandLine());
    }
}
