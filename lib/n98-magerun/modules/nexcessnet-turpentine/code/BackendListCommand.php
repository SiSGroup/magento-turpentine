<?php

namespace Nexcessnet\Turpentine\Command\Varnish;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class BackendListCommand extends AbstractBackendCommand
{
    protected function configure()
    {
        $this
            ->setName('varnish:backend:list')
            ->setDescription('List backend nodes')
            ->setHelp(<<<EOT
Show the current backend node list.
EOT
                )
            ->addOption('cached', null, InputOption::VALUE_NONE, 'Show cached node list as seen by the Varnish Configurator')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
			return;
		}

        $cached = $input->getOption('cached');

        $nodes = $this->_getBackendNodes($cached);

        if (count($nodes) == 0) {
            $output->writeln( '<info>The backend node list is empty</info>' );
            return;
        }

        $n = 0;
        foreach ($nodes as $node) {
            $output->writeln( sprintf('<info>web%d</info>: <info>%s</info>', $n++, $node) );
        }
    }
}
