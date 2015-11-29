<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 2:51 AM
 */

namespace mglaman\PlatformDocker\Command\Flamegraph;

use mglaman\PlatformDocker\Command\Command;
use mglaman\PlatformDocker\Platform;
use mglaman\PlatformDocker\Stacks\StacksFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use mglaman\Toolstack\Stacks;

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
        $stack = StacksFactory::getStack(Platform::webDir());
        switch ($stack->type()) {
            case Stacks\Drupal::TYPE:
                $this->stdOut->writeln("<comment>Removing patch on Drupal for xhprof</comment>");
                $patchProcess = new Process(
                  'patch -p1 -R < ' . CLI_ROOT . '/resources/drupal-enable-profiling.patch',
                  Platform::webDir()
                );
                break;
            case Stacks\WordPress::TYPE:
                $this->stdOut->writeln("<comment>Removing patch on WordPress for xhprof</comment>");
                $patchProcess = new Process(
                  'patch -p0 -R < ' . CLI_ROOT . '/resources/wordpress-enable-profiling.patch',
                  Platform::webDir()
                );
                break;
            default:
                throw new \Exception('Stack type not supported yet.');
        }
        $patchProcess->mustRun(null);
    }
}
