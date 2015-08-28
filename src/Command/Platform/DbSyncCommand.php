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
use Platformsh\Cli\Helper\ShellHelper;
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

        /** @var ShellHelper $shell */
        $shell = $this->getHelper('shell');
        $shell->execute([
            'platform',
            'sql-dump',
        ]);

        $cd = getcwd();
        chdir(Platform::webDir());
        exec('drush sqlc < ../dump.sql');
        chdir($cd);

        // Why can't I get execute() w/ < to work.
//        $shell->execute([
//            'drush',
//            'sqlc < ../dump.sql',
//        ], Platform::webDir());
    }
}
