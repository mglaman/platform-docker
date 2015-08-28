<?php

namespace mglaman\PlatformDocker\Command;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Yaml;

/**
 * Class InitCommand
 * @package mglaman\PlatformDocker\Command\Docker
 */
class InitCommand extends Command
{
    /**
     * @var Filesystem;
     */
    protected $fs;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
          ->setName('init')
          ->setDescription('Setup Platform and Docker Compose files');
    }
    /**
     * @inheritdoc
     */
    protected function initialize(InputInterface $input, OutputInterface $output) {
        $this->fs = new Filesystem();
        if (empty($this->projectConfig)) {
            /** @var QuestionHelper $helper */
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion("<info>There isn't a project here, create one? [Y,n] </info>");

            if ($helper->ask($input, $output, $question)) {
                // Reset project path since we're making project here.
                $this->projectPath = getcwd();

                $question = new Question("<comment>Project name (machine name)</comment>: ");
                $this->projectConfig['alias-group'] = $helper->ask($input, $output, $question);

                $dumper = new Dumper();
                file_put_contents($this->projectPath . '/.platform-project', $dumper->dump($this->projectConfig, 2));
                clearstatcache(true);
            }
            else {
                exit(1);
            }
        }

        parent::initialize($input, $output);
    }


    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // If the docker-compose.yml file exists, then start containers.
        if ($this->fs->exists($this->projectPath . '/docker-compose.yml')) {
            $this->stdOut->writeln("<info>Docker compose initiated, starting containers. Run docker:rebuild to rebuild.");
            return $this->getApplication()->find('docker:up')->run($input, $output);
        }

        $this->getApplication()->find('docker:rebuild')->run($input, $output);
        sleep(5);
        $this->getApplication()->find('platform:db-sync')->run($input, $output);
    }


}
