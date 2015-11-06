<?php

namespace DRI\SugarCRM\Plugin\Command;

use DRI\SugarCRM\Plugin\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @author Emil Kilhage
 */
abstract class AbstractCommand extends Command
{
    /**
     * @return Config
     */
    protected function getConfig()
    {
        $config = Config::factory();

        return $config;
    }

    /**
     * @param InputInterface $input
     *
     * @return Config
     */
    protected function setup(InputInterface $input)
    {
        $config = $this->getConfig();

        $flavours = $input->getOption('flav');

        foreach ($flavours as $flavour) {
            $config->mergeFlavour($flavour);
        }

        return $config;
    }
}
