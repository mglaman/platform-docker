<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 2:51 AM
 */

namespace Platformsh\Docker\Command\Flamegraph;

use Platformsh\Docker\Command\Command;
use Platformsh\Docker\Utils\Docker\Docker;
use Platformsh\Docker\Utils\Platform\Platform;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class UnpatchCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
          ->setName('flamegraph:unpatch')
          ->setDescription('Unpatches index.php to stop xhprof logging.');
    }

    /**
     * {@inheritdoc}
     *
     * @see PlatformCommand::getCurrentEnvironment()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->stdOut->writeln("<comment>Removing patch on Drupal for xhprof</comment>");
        $process = new Process(
          'patch -p1 -R < ' . CLI_ROOT . '/resources/drupal-enable-profiling.patch',
          Platform::webDir()
        );
        $process->mustRun(null);
    }
}
