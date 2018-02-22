<?php

namespace Nexcessnet\Turpentine\Command\Varnish;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class ApplyCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this
            ->setName('varnish:apply')
            ->setDescription('Apply varnish config to varnish servers')
            ->setHelp(<<<EOT
Updates all of the varnish servers with the current varnish VCL.

If you would like to only update one server provide it as an argument.
EOT
                )
            ->addArgument('server', InputArgument::OPTIONAL, 'A specific server')
        ;
    }

    /**
     * @param InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if ($this->initMagento()) {
            \Mage::dispatchEvent( 'turpentine_varnish_apply_config' );
            $result = \Mage::getModel( 'turpentine/varnish_admin' )->applyConfig();
            foreach( $result as $name => $value ) {
                if( $value === true ) {
                    $output->writeln( sprintf( '<info>VCL successfully applied to %s</info>', $name ) );
                } else {
                    $output->writeln( sprintf( '<error>Failed to apply the VCL to %s: %s</error>', $name, $value ) );
                }
            }
        }
    }
}
