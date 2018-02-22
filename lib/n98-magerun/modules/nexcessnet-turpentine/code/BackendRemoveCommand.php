<?php

namespace Nexcessnet\Turpentine\Command\Varnish;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class BackendRemoveCommand extends AbstractBackendCommand
{
    protected function configure()
    {
        $this
            ->setName('varnish:backend:remove')
            ->setDescription('Remove a backend node')
            ->setHelp(<<<EOT
Remove an entry from the backend node list.

   $ n98-magerun.phar varnish:backend:remove 203.0.113.1:80
EOT
                )
            ->addOption('skip-clean', null, InputOption::VALUE_NONE, 'Skip cleaning of the config cache after updating')
            ->addOption('skip-event', null, InputOption::VALUE_NONE, 'Skip sending config change event (implied by --skip-clean)')
            ->addArgument('node', InputArgument::REQUIRED, 'The node to remove')
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

        $skipClean = $input->getOption('skip-clean');
        $skipEvent = $input->getOption('skip-event');

        $node = $input->getArgument('node');
        $nodes = $this->_getBackendNodes();

        if (!in_array($node, $nodes)) {
            $output->writeln( sprintf( "<info>Node '%s' is not currently a backend node</info>", $node ) );
            return;
        }

        $nodes = array_diff( $nodes, array($node) );
        if (!$this->_setBackendNodes($nodes, !$skipClean, !$skipEvent)) {
            $output->writeln( sprintf( "<info>Failed to remove node '%s' from the backend node list</info>", $node ) );
            return;
        }

        $output->writeln( sprintf( "<info>Node '%s' has been removed from the backend node list</info>", $node ) );
    }
}
