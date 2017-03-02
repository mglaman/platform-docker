<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 2:51 AM
 */

namespace mglaman\PlatformDocker\Command;

use mglaman\Docker\Compose;
use mglaman\PlatformDocker\BrowserTrait;
use mglaman\PlatformDocker\Command\Docker\DockerCommand;
use mglaman\PlatformDocker\Platform;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use mglaman\Docker\Docker;

class LinkCommand extends DockerCommand
{
    use BrowserTrait;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
          ->setName('link')
          ->setDescription('Displays link to local environment, with port.');
    }

    /**
     * {@inheritdoc}
     *
     * @see PlatformCommand::getCurrentEnvironment()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $url = Platform::getUri();
        } catch (\Exception $e) {
            $output->writeln('<error>The nginx container is not running.</error>');
        }

        try {
            // See if the nginx-proxy is running and use that if it is.
            $process = Docker::inspect(['--format="{{ .State.Running }}"', 'nginx-proxy'], true);
            if (trim($process->getOutput()) === 'true') {
                $url = 'http://' . Platform::projectName() . '.' . Platform::projectTld();
            }
        }
        catch (\Exception $e) {}

        if ($url) {
            $this->openUrl($url, $this->stdErr, $this->stdOut);
        }
    }
}
