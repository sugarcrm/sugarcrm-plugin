<?php

namespace DRI\SugarCRM\Plugin\Command;

use DRI\SugarCRM\Plugin\Cli;
use DRI\SugarCRM\Plugin\Path;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Emil Kilhage
 */
class SymlinkCommand extends AbstractCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('symlink');
        $this->addArgument('target', InputArgument::REQUIRED, 'Target sugarcrm path');
        $this->setDescription('Symlinks the plugin source to a sugarcrm project');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $target = $input->getArgument('target');
        $root = Path::getRootPath();

        clean("src");

        Cli::exec("rm -rf $target/custom");
        Cli::exec("rm -rf $target/modules/ibm_connections*");

        Cli::exec("ln -fs $root/src/custom $target/custom");
        Cli::exec("ln -fs $root/src/modules/ibm_connections* $target/modules");
    }
}
