<?php

namespace DRI\SugarCRM\Plugin;

/**
 * @author Emil Kilhage
 */
class ArrayUtils
{
    /**
     * @param array $gimp the array whose values will be overloaded
     * @param array $dom the array whose values will pwn the gimp's
     * @return array beaten gimp
     */
    public static function mergeRecursive(array $gimp, array $dom)
    {
        foreach ($dom as $k => $v) {
            if (isset($gimp[$k])) {
                if (is_array($v) && is_array($gimp[$k])) {
                    $gimp[$k] = self::mergeRecursive($gimp[$k], $v);
                } elseif (is_int($k)) {
                    $gimp[] = $v;
                } else {
                    $gimp[$k] = $v;
                }
            } else {
                $gimp[$k] = $v;
            }
        }

        return $gimp;
    }
}
