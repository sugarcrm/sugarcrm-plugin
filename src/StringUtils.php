<?php

namespace DRI\SugarCRM\Plugin;

/**
 * @author Emil Kilhage
 */
class StringUtils
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
