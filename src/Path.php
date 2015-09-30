<?php

namespace DRI\SugarCRM\Plugin;

/**
 * @author Emil Kilhage
 */
class Path
{
    /**
     * @var string
     */
    private static $rootPath;

    /**
     * @return string
     */
    public static function getRootPath()
    {
        return self::$rootPath;
    }

    /**
     * @param string $rootPath
     */
    public static function setRootPath($rootPath)
    {
        self::$rootPath = $rootPath;
    }
}
