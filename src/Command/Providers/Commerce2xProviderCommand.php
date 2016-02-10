<?php
/**
 * Created by PhpStorm.
 * User: mglaman
 * Date: 2/3/16
 * Time: 6:55 AM
 */

namespace mglaman\PlatformDocker\Command\Providers;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;

class Commerce2xProviderCommand extends ProviderCommand
{
    function providerCommandName()
    {
        return 'commerce2x';
    }

    function providerName()
    {
        return 'Drupal Commerce 2.x';
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $builder = ProcessBuilder::create([
            'composer',
            'create-project',
            'drupalcommerce/project-base',
            $input->getArgument('project'),
            '--stability',
            'dev'
        ]);
        $builder->setTimeout(null);
        $builder->enableOutput();
        $process = $builder->getProcess();
        $process->run();

        if (!$process->isSuccessful()) {
            $output->writeln($process->getErrorOutput());
        } else {
            $output->writeln($process->getOutput());
        }

    }
}
