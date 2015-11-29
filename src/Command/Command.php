<?php

namespace mglaman\PlatformDocker\Command;


use mglaman\PlatformDocker\Config;
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
    protected $projectRequired = true;

    /**
     * @inheritdoc
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->stdOut = $output;
        $this->stdErr = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
        $this->stdIn = $input;
        self::$interactive = $input->isInteractive();

        // Check if this command requires a project to be defined in order to run.
        $this->checkProjectRequired();
    }

    protected function checkProjectRequired() {
        if ($this->projectRequired && empty(Config::get())) {
            $this->getApplication()->find('init')->run($this->stdIn, $this->stdOut);
            exit(1);
        }
    }
}
