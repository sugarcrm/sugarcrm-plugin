<?php

namespace DRI\SugarCRM\Plugin\Command;

use DRI\SugarCRM\Plugin\Config;
use Symfony\Component\Console\Command\Command;

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
}
