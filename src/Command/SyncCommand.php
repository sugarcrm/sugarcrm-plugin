<?php

namespace DRI\SugarCRM\Plugin\Command;

use DRI\SugarCRM\Plugin\Cli;
use DRI\SugarCRM\Plugin\Path;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Emil Kilhage
 */
class SyncCommand extends AbstractCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('sync');
        $this->addArgument('target', InputArgument::REQUIRED, 'Target sugarcrm path');
        $this->addOption('back', 'B', InputOption::VALUE_NONE, 'changes direction: plugin <- sugarcrm');
        $this->setDescription('Synchronizes changes between the plugin source and a sugarcrm project (default direction: plugin -> sugarcrm)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $target = $input->getArgument('target');
        $root = Path::getRootPath();

        if ($input->getOption('back')) {
            Cli::exec("rsync -r $target/custom/ $root/src/custom");
            Cli::exec("rsync -r $target/modules/ibm_connections* $root/src/modules");

            clean("$root/src");
        } else {
            clean("$root/src");

            if (is_link("$target/custom")) {
                Cli::exec("rm $target/custom");
            }

            Cli::exec("rsync -r $root/src/custom/ $target/custom");
            Cli::exec("rsync -r $root/src/modules/ $target/modules");
        }
    }
}
