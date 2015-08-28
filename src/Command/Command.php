<?php

namespace mglaman\PlatformDocker\Command;

use mglaman\PlatformDocker\Utils\Platform\Platform;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Yaml\Parser;

abstract class Command extends BaseCommand
{
    /** @var OutputInterface|null */
    protected $stdOut;
    /** @var OutputInterface|null */
    protected $stdErr;
    /** @var  InputInterface|null */
    protected $stdIn;
    /** @var bool */
    protected static $interactive = false;
    /** @var array */
    protected $projectConfig = array();
    protected $projectName;
    protected $projectPath;


    /**
     * @inheritdoc
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->stdOut = $output;
        $this->stdErr = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
        $this->stdIn = $input;
        self::$interactive = $input->isInteractive();

        // Validate we can run.
        $this->validate();

        $this->projectPath = Platform::rootDir();
        $this->projectName = Platform::projectName();
    }

    protected function validate()
    {
        if (empty($this->projectConfig)) {
            if (!$this->projectConfig = $this->loadConfig()) {
                $this->stdOut->writeln('<error>Must run command within a project</error>');
                exit(1);
            }
        }
        return false;
    }

    protected function loadConfig()
    {
        if (file_exists('.platform-project')) {
            $yaml = new Parser();
            return $yaml->parse(file_get_contents('.platform-project'));
        }
        return array();
    }
}
