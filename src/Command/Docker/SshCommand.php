<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 12:13 AM
 */

namespace mglaman\PlatformDocker\Command\Docker;

use mglaman\Docker\Compose;
use mglaman\PlatformDocker\Docker;
use mglaman\PlatformDocker\Platform;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ProcessBuilder;

class SshCommand extends DockerCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
          ->setName('docker:ssh')
            ->setAliases(['ssh'])
          ->setDescription('Allows for quick SSH into a service container.')
          ->addArgument(
            'service',
            InputArgument::REQUIRED,
            'Service to SSH into the container of: http, php, db, redis, solr, memcache');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $containerName = null;
        $type = $input->getArgument('service');

        $containerNameMap = [
            'http' => 'nginx',
            'php' => 'phpfpm',
            'db' => 'mariadb',
            'redis' => 'redis',
            'solr' => 'solr',
            'memcache' => 'memcached',
            'blackfire' => 'blackfire',
        ];

        if (!isset($containerNameMap[$type])) {
            $this->stdOut->writeln("<error>Invalid service type</error>");
            return 1;
        } else {
            $containerName = $containerNameMap[$type];
        }

        $builder = ProcessBuilder::create([
          'docker',
          'exec',
          '-it',
          Compose::getContainerName(Platform::projectName(), $containerName),
          'bash'
        ]);

        $process = $builder->getProcess();
        // Need to set tty true, ProccessHelper doesn't allow this setting.
        $process->setTty(true);
        try {
            $process->mustRun(null);
        } catch(ProcessFailedException $e) {
            $message = "The command failed with the exit code: " . $process->getExitCode();
            $message .= "\n\nFull command: " . $process->getCommandLine();
            throw new \Exception($message);
        }

        return $process->isSuccessful();
    }
}
