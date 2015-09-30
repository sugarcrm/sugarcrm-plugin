<?php

namespace DRI\SugarCRM\Plugin\Command;

use DRI\SugarCRM\Plugin\Config;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use DRI\SugarCRM\Plugin\PackageCreator;

/**
 * @author Emil Kilhage
 */
class CreatePackageCommand extends AbstractCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('create');

        $this->addOption(
            'flav',
            'F',
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'package flavour(s) to use'
        );

        $this->setDescription('Creates a package');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->setup($input);
        $packageCreator = new PackageCreator($config);
        $packageCreator->create();
    }

    /**
     * @param InputInterface $input
     *
     * @return Config
     */
    protected function setup(InputInterface $input)
    {
        $config = Config::factory();

        $flavours = $input->getOption('flav');

        foreach ($flavours as $flavour) {
            $config->mergeFlavour($flavour);
        }

        return $config;
    }
}
