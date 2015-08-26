<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 8/25/15
 * Time: 1:59 AM
 */

namespace Platformsh\Docker\Command\Platform;


use Platformsh\Docker\Command\Command;
use Platformsh\Docker\Utils\Platform\Platform;
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

        // @todo this isn't as robust as getCurrentEnvironment().
        $git = $this->getHelper('shell');
        $currentBranch = $git->execute(
          ['git', 'symbolic-ref', '--short', 'HEAD'], Platform::repoDir());

        $remoteAlias = '@' . Platform::projectName() . '.' . $currentBranch;
        $localAlias = '@' . Platform::projectName() . '._local';

        // @todo squeeze this into the ShellHelper arguments somehow.
        exec("drush $remoteAlias sql-dump --gzip | gzip -cd | drush $localAlias sqlc");
//        $this->stdOut->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
//        $shell = new ShellHelper($this->stdOut);
//        $shell->execute([
//            "drush $remoteAlias sql-dump --gzip | gzip -cd | drush $localAlias sqlc"
//        ], null, true, false);
    }
}
