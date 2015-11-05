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
class PackageCommand extends AbstractCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('package');

        $this->addOption(
            'flav',
            'F',
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'package flavour(s) to use'
        );

        $this->addOption('force', '', InputOption::VALUE_NONE, 'force the install to overwrite existing packages');

        $this->setDescription('Creates a package');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->setup($input);
        $config->set('force', $input->getOption('force'));

        $packageCreator = new PackageCreator($config);
        $packageCreator->create();

        $output->writeln("<info>Your new package has been created here: {$packageCreator->getTargetZipPath()}</info>");
    }
}
