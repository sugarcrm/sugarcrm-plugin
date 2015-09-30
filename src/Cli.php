<?php

namespace DRI\SugarCRM\Plugin;

/**
 * @author Emil Kilhage
 */
class Cli
{
    /**
     * @param string $cmd
     */
    public static function exec($cmd)
    {
        echo ">>> $cmd\n";
        exec("$cmd");
    }
}
