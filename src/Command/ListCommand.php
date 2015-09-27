<?php

namespace mglaman\PlatformDocker\Command;

use mglaman\PlatformDocker\Descriptor\CustomTextDescriptor;
use Symfony\Component\Console\Command\ListCommand as BaseListCommand;
use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends BaseListCommand
{
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('xml')) {
            @trigger_error('The --xml option was deprecated in version 2.7 and will be removed in version 3.0. Use the --format option instead.', E_USER_DEPRECATED);

            $input->setOption('format', 'xml');
        }

        $helper = new DescriptorHelper();
        $helper->register('txt', new CustomTextDescriptor());
        $helper->describe($output, $this->getApplication(),
          array(
            'format' => $input->getOption('format'),
            'raw_text' => $input->getOption('raw'),
            'namespace' => $input->getArgument('namespace'),
          )
        );
    }
}
