<?php

namespace mglaman\PlatformDocker\Command\Providers;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;

class PlatformshProviderCommand extends ProviderCommand {
    function providerCommandName()
    {
        return 'platformsh';
    }

    function providerName()
    {
        return 'Platform.sh';
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $builder = ProcessBuilder::create([
          'platform',
          'get',
          $input->getArgument('project'),
          '--yes'
        ]);
        $builder->setTimeout(null);
        $builder->enableOutput();
        $process = $builder->getProcess();
        $process->run();

        if (!$process->isSuccessful()) {
            $output->writeln($process->getErrorOutput());
        }
        else {
            $buildDirectory = '';
            $platformOutput = $process->getOutput();
            foreach (explode(PHP_EOL, $platformOutput) as $input_line) {
                preg_match("/^    @(.*)._local$/", $input_line, $output_array);
                if (isset($output_array[1])) {
                    $buildDirectory = $output_array[1];
                    $output->writeln("Folder name is <info>{$output_array[1]}</info>");
                }
            }
            $this->runBuild($buildDirectory);
        }

    }

}
