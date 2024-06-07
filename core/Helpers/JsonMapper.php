<?php
namespace App\Helpers;

use App\Entity\AbstractEntity;

final class JsonMapper
{
    /**
     * @return AbstractEntity[]
     */
    public static function map_array(string $json, string $entity): array
    {
        $datas = json_decode($json, true);
        $entities = [];
        if ($datas):
            foreach ($datas as $data):
                $class = new $entity;
                foreach ($data as $key => $value):
                    $method = "set_$key";
                    $class->$method($value);
                endforeach;
                $entities[] = $class;
            endforeach;
        endif;
        return $entities;
    }
}
