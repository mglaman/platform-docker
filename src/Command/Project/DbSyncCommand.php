<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 1:59 AM
 */

namespace mglaman\PlatformDocker\Command\Project;


use mglaman\PlatformDocker\Command\Command;
use mglaman\PlatformDocker\Utils\Platform\Config;
use mglaman\PlatformDocker\Utils\Platform\Platform;
use Symfony\Component\Console\Input\InputArgument;
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
          ->setName('project:db-sync')
          ->addArgument('file', InputArgument::OPTIONAL, 'File path of SQL dump to import, defaults to ../dump.sql', '../dump.sql')
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

        // If this is a Platform.sh project, get latest dump.
        // @todo: add proper provider integration
        if (Config::get('id')) {
            $process->mustRun($this->stdOut, [
              'platform',
              'sql-dump',
            ]);
        }

        $cd = getcwd();
        chdir(Platform::webDir());
        $process->run($this->stdOut, 'drush sqlc < ' . $input->getArgument('file'));
        chdir($cd);
    }
}
