<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 2:51 AM
 */

namespace mglaman\PlatformDocker\Command\Flamegraph;

use Platformsh\Cli\Helper\ShellHelper;
use Platformsh\Cli\Local\LocalProject;
use mglaman\PlatformDocker\Command\Command;
use mglaman\PlatformDocker\Utils\Platform\Platform;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class SetupCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
          ->setName('flamegraph:setup')
          ->setDescription('Sets the project up for generating flamegrapghs.');
    }

    /**
     * {@inheritdoc}
     *
     * @see PlatformCommand::getCurrentEnvironment()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ShellHelper $shell */
        $shell = $this->getHelper('shell');
        $output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
        $shell->setOutput($output);
        if (!is_dir(LocalProject::getProjectRoot() . '/docker/fg')) {
            $shell->execute([
                'git',
                'clone',
                'https://github.com/brendangregg/FlameGraph.git',
              LocalProject::getProjectRoot() . '/docker/fg'
            ]);
        }
        if (!is_dir(LocalProject::getProjectRoot() . '/docker/xhpfg')) {
            $shell->execute([
              'git',
              'clone',
              'https://github.com/msonnabaum/xhprof-flamegraphs.git',
              LocalProject::getProjectRoot() . '/docker/xhpfg'
            ]);
        }

        $this->stdOut->writeln("<comment>Patching Drupal for xhprof</comment>");
        $process = new Process(
          'patch -p1 < ' . CLI_ROOT . '/resources/drupal-enable-profiling.patch',
          Platform::webDir()
          );
        $process->mustRun(null);
    }
}
