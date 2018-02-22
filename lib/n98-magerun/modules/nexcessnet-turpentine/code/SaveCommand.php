<?php

namespace Nexcessnet\Turpentine\Command\Varnish;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class SaveCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this
            ->setName('varnish:save')
            ->setDescription('Save the configuration to a VCL file')
            ->setHelp(<<<EOT
Saves the current varnish configuration VCL to a file
EOT
                )
            #->addArgument('file', InputArgument::OPTIONAL, 'Where to save the VCL')
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
            $cfgr = \Mage::getModel( 'turpentine/varnish_admin' )->getConfigurator();
            if( is_null( $cfgr ) ) {
                $output->writeln( sprintf( '<error>Failed to load configurator</error>' ) );
            } else {
                \Mage::dispatchEvent( 'turpentine_varnish_save_config', array( 'cfgr' => $cfgr ) );
                $result = $cfgr->save( $cfgr->generate( \Mage::helper('turpentine')->shouldStripVclWhitespace('save') ) );
                if( $result[0] ) {
                    $output->writeln( sprintf( '<info>The VCL file has been saved.</info>' ) );
                } else {
                    $output->writeln( sprintf( '<error>Failed to save the VCL file: %s</error>', $result[1]['message'] ) );
                }
            }
        }
    }
}
