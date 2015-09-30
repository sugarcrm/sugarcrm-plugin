<?php

namespace DRI\SugarCRM\Plugin\Command;

use DRI\SugarCRM\Plugin\Cli;
use DRI\SugarCRM\Plugin\Config;
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

        $config = $this->getConfig();

        foreach ($config->get('dev') as $source => $remote) {
            Cli::exec("rm -rf $target/$source");
        }

        foreach ($config->get('dev') as $source => $remote) {
            Cli::exec("ln -fs $root/$source $target/$remote");
        }
    }
}
