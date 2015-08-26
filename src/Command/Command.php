<?php

namespace Platformsh\Docker\Command;

use Platformsh\Cli\Local\LocalProject;
use Platformsh\Docker\Utils\Platform\Platform;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

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
    protected $platformConfig;
    protected $projectName;
    protected $projectPath;

    /**
     * {@inheritdoc}
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->getProjectConfig();
    }


    /**
     * @inheritdoc
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        // Validate we can run.
        if ($this->platformConfig === null) {
            $output->writeln('<error>Must run command within a Platform.sh project</error>');
            exit(1);
        }

        $this->projectPath = LocalProject::getProjectRoot();
        $this->projectName = Platform::projectName();

        $this->stdOut = $output;
        $this->stdErr = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
        $this->stdIn = $input;
        self::$interactive = $input->isInteractive();
    }

    /**
     * Returns current project's Platform.sh config.
     *
     * @throws \Exception
     */
    protected function getProjectConfig()
    {
        $this->platformConfig = LocalProject::getProjectConfig();
    }
}
