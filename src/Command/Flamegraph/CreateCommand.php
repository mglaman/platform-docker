<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 2:51 AM
 */

namespace mglaman\PlatformDocker\Command\Flamegraph;

use Platformsh\Cli\Local\LocalProject;
use mglaman\PlatformDocker\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
          ->setName('flamegraph:create')
          ->addArgument('filename', InputArgument::REQUIRED)
          ->setDescription('Creates a flamegraph from xhprof folder contents.');
    }

    /**
     * {@inheritdoc}
     *
     * @see PlatformCommand::getCurrentEnvironment()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $xhpfg = LocalProject::getProjectRoot() . '/docker/xhpfg/xhprof-sample-to-flamegraph-stacks';
        $fg = LocalProject::getProjectRoot() . '/docker/fg/flamegraph.pl';
        $xhprof = LocalProject::getProjectRoot() . '/xhprof';
        $graphName = $input->getArgument('filename');
        $graphDestination = LocalProject::getProjectRoot() . '/' . $graphName . '.svg';

        exec("$xhpfg $xhprof | $fg > $graphDestination");

        $url = 'file://' . $graphDestination;
        $this->openUrl($url);
    }

    /**
     * Open a URL in the browser, or print it.
     *
     * @param string          $url
     */
    protected function openUrl($url)
    {
        $shellHelper = $this->getHelper('shell');

        $browser = $this->getDefaultBrowser();
        if ($browser) {
            $opened = $shellHelper->execute(array($browser, $url));
            if ($opened) {
                $this->stdErr->writeln("<info>Opened</info>: $url");
                return;
            }
        } else {
            $this->stdErr->writeln("<error>Browser not found: $browser</error>");
        }
        $this->stdOut->writeln($url);
    }

    /**
     * Find a default browser to use.
     *
     * @return string|false
     */
    protected function getDefaultBrowser()
    {
        $potential = array('xdg-open', 'open', 'start');
        $shellHelper = $this->getHelper('shell');
        foreach ($potential as $browser) {
            if ($shellHelper->commandExists($browser)) {
                return $browser;
            }
        }
        return false;
    }
}
