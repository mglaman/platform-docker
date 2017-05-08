<?php

namespace mglaman\PlatformDocker\Command;

use mglaman\PlatformDocker\Config;
use mglaman\PlatformDocker\Platform;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Class InitCommand
 * @package mglaman\PlatformDocker\Command\Docker
 */
class InitCommand extends Command
{

    protected $cwd;

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
        if (empty(Config::get())) {
            $this->cwd = getcwd();

            /** @var QuestionHelper $helper */
            $helper = $this->getHelper('question');
            $output->writeln("<comment>Current directory: {$this->cwd}");
            $question = new ConfirmationQuestion("<info>There isn't a project here, create one? [Y,n] </info>");

            if ($helper->ask($input, $output, $question)) {
                Config::set('alias-group', basename($this->cwd));
                Config::set('name', basename($this->cwd));
                Config::set('path', $this->cwd);

                // Platform.sh scaffold docroot.
                if (is_dir($this->cwd . '/' . Platform::DEFAULT_WEB_ROOT)) {
                  Config::set('docroot', Platform::DEFAULT_WEB_ROOT);
                }
                // Typical app location.
                elseif (is_dir($this->cwd . '/web')) {
                    Config::set('docroot', 'web');
                }
                // Acquia.
                elseif (is_dir($this->cwd . '/docroot')) {
                    Config::set('docroot', 'docroot');
                }
                else {
                  $question = new Question("<info>What is the document root for the project?");
                  $answer = $helper->ask($input, $output, $question);
                  Config::set('docroot', $answer);
                }

                if (!Config::write($this->cwd)) {
                    throw new \Exception('There was an error writing the platform configuration.');
                }
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
        if (file_exists($this->cwd . '/docker-compose.yml')) {
            $this->stdOut->writeln("<info>Docker compose initiated, starting containers. Run docker:rebuild to rebuild.");
            return $this->getApplication()->find('docker:up')->run($input, $output);
        }

        return $this->getApplication()->find('docker:rebuild')->run($input, $output);
    }


}
