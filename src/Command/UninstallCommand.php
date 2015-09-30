<?php

namespace DRI\SugarCRM\Plugin\Command;

use DRI\SugarCRM\Plugin\Cli;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Emil Kilhage
 */
class UninstallCommand extends AbstractCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('uninstall');
        $this->addArgument('target', InputArgument::REQUIRED, 'Target sugarcrm path');
        $this->setDescription('Uninstalls the plugin source from a sugarcrm project');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $target = $input->getArgument('target');

        Cli::exec("rm -rf $target/custom");
        Cli::exec("rm -rf $target/modules/ibm_connections*");
        Cli::exec("rm -rf $target/tests/custom/clients/base/fields/memberset");
        Cli::exec("rm -rf $target/tests/custom/clients/base/views/ibm-connections");
        Cli::exec("rm -rf $target/tests/modules/ibm_connections*");
    }
}
