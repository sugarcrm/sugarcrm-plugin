<?php

namespace DRI\SugarCRM\Plugin\Command;

use DRI\SugarCRM\Plugin\Path;
use DRI\SugarCRM\Tests\Bootstrap;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Emil Kilhage
 */
class ActionCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('action');
        $this->addArgument('target', InputArgument::REQUIRED, 'Target sugarcrm path');
        $this->addArgument('name', InputArgument::REQUIRED, 'Action command');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->getConfig();
        $root = Path::getRootPath();

        $scripts = $config->get('installdefs.'.$input->getArgument('name'));

        $scripts = str_replace('<basepath>', $root, $scripts);

        chdir($input->getArgument('target'));

        Bootstrap::bootSugar();
        Bootstrap::initAdminUser();

        foreach ($scripts as $script) {
            require $script;
        }
    }
}
