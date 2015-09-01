<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 2:51 AM
 */

namespace mglaman\PlatformDocker\Command\Flamegraph;

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
        /** @var \Symfony\Component\Console\Helper\ProcessHelper $process */
        $process = $this->getHelper('process');
        $output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
        if (!is_dir(Platform::rootDir() . '/docker/fg')) {
            $process->mustRun($output, [
                'git',
                'clone',
                'https://github.com/brendangregg/FlameGraph.git',
              Platform::rootDir() . '/docker/fg'
            ]);
        }
        if (!is_dir(Platform::rootDir() . '/docker/xhpfg')) {
            $process->mustRun($output, [
              'git',
              'clone',
              'https://github.com/msonnabaum/xhprof-flamegraphs.git',
              Platform::rootDir() . '/docker/xhpfg'
            ]);
        }

        $this->stdOut->writeln("<comment>Patching Drupal for xhprof</comment>");
        $patchProcess = new Process(
          'patch -p1 < ' . CLI_ROOT . '/resources/drupal-enable-profiling.patch',
          Platform::webDir()
          );
        $patchProcess->mustRun();
    }
}
