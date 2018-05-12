<?php
/**
 * Created by PhpStorm.
 * User: janwaldecker
 * Date: 12.05.18
 * Time: 18:20
 */

namespace NicAPI;


class DateTimeMigrator
{

    public static function formatValues(array $array)
    {
        foreach ($array as $key => $item) {
            if (is_array($item)) {
                $array[$key] = self::formatValues($item);
            } else {
                if ($item instanceof \DateTime)
                    $array[$key] = $item->format("Y-m-d H:i:s");
            }
        }

        return $array;
    }
}