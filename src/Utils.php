<?php

namespace DRI\SugarCRM\Plugin;

/**
 * @author Emil Kilhage
 */
class Utils
{
    /**
     * @param string $path
     *
     * @return bool
     */
    public static function isWildcardPath($path)
    {
        return substr($path, -1, 1) === '*';
    }
}
