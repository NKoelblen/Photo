<?php
namespace App\Helpers;

class ArrayHelper
{
    public static function diverse_array(array $array): array
    {
        $result = [];
        foreach ($array as $key1 => $value1):
            foreach ($value1 as $key2 => $value2):
                $result[$key2][$key1] = $value2;
            endforeach;
        endforeach;
        return $result;
    }
}