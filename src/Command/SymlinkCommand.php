<?php

namespace DRI\SugarCRM\Plugin\Command;

use DRI\SugarCRM\Plugin\Cli;
use DRI\SugarCRM\Plugin\Path;
use DRI\SugarCRM\Plugin\StringUtils;
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
            Cli::exec("rm -rf $target/$remote");
        }

        foreach ($config->get('dev') as $source => $remote) {
            $from = "$root/$source";
            $to = "$target/$remote";

            if (StringUtils::isWildcardPath($to)) {
                $to = dirname($to);
            }

            Cli::exec("ln -fs $from $to");
        }
    }
}
