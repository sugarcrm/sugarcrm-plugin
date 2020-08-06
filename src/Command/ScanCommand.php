<?php

namespace DRI\SugarCRM\Plugin\Command;
use DRI\SugarCRM\Plugin\PackageCreator;
use DRI\SugarCRM\Plugin\Path;
use DRI\SugarCRM\Tests\Bootstrap;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Emil Kilhage
 */
class ScanCommand extends AbstractCommand
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('scan');

        $this->addArgument('target', InputArgument::REQUIRED, 'Target sugarcrm path');

        $this->addOption(
            'flav',
            'F',
            InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
            'package flavour(s) to use'
        );

        $this->setDescription('Scans a package');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->setup($input);
        $root = Path::getRootPath();
        chdir($input->getArgument('target'));

        Bootstrap::bootSugar();

        $config->set('overwrite', true);

        $packageCreator = new PackageCreator($config);
        $packageCreator->package();

        require_once "ModuleInstall/ModuleScanner.php";

        $scanner = new \ModuleScanner();

        $scanner->scanManifest("$root/package/manifest.php");
        $scanner->scanDir("$root/package");

        if ($scanner->hasIssues()) {
            $output->writeln('<error>Errors found in package:</error>');
            print_r($scanner->getIssues());
            return 1;
        } else {
            $output->writeln('<info>Package is clean</info>');
        }
    }
}
