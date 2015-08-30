<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 1:59 AM
 */

namespace mglaman\PlatformDocker\Command\Platform;


use mglaman\PlatformDocker\Command\Command;
use mglaman\PlatformDocker\Utils\Platform\Platform;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DbSyncCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
          ->setName('platform:db-sync')
          ->setDescription('Syncs database from environment to local');
    }

    /**
     * {@inheritdoc}
     *
     * @see PlatformCommand::getCurrentEnvironment()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->stdOut->writeln("<info>Syncing Platform.sh environment database to local</info>");

        /** @var \Symfony\Component\Console\Helper\ProcessHelper $process */
        $process = $this->getHelper('process');
        $process->mustRun($this->stdOut, [
            'platform',
            'sql-dump',
        ]);

        $cd = getcwd();
        chdir(Platform::webDir());
        $process->run($this->stdOut, 'drush sqlc < ../dump.sql');
        chdir($cd);
    }
}
