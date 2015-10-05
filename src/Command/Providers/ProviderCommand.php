<?php

namespace mglaman\PlatformDocker\Command\Providers;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ProviderCommand extends BaseCommand {
    /** @var OutputInterface|null */
    protected $stdOut;
    /** @var OutputInterface|null */
    protected $stdErr;
    /** @var  InputInterface|null */
    protected $stdIn;
    /** @var bool */
    protected static $interactive = false;

    /**
     * @inheritdoc
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->stdOut = $output;
        $this->stdErr = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
        $this->stdIn = $input;
        self::$interactive = $input->isInteractive();
    }


    abstract function providerCommandName();
    abstract function providerName();

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
          ->setName('provider:' . $this->providerCommandName())
          ->addArgument('project', InputArgument::REQUIRED, 'Project identifier')
          ->setDescription("Sets up a {$this->providerName()} project");

    }

    protected function runBuild($buildDirectory) {
//        $fakeInput = new ArgvInput();
//        return $this->getApplication()->find('docker:rebuild')->run($fakeInput, $output);
        chdir($buildDirectory);
        passthru('platform-docker docker:rebuild');
    }
}
