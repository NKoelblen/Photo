<?php
namespace App\Helpers;

use App\Entity\AbstractEntity;

final class JsonMapper
{
    public static function map(string $json, string $entity): AbstractEntity
    {
        $data = json_decode($json, true);
        if ($data):
            $class = new $entity;
            foreach ($data as $key => $value):
                $method = "set_$key";
                $class->$method($value);
            endforeach;
        endif;
        return $class;
    }

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
                if (is_string($data)):
                    $data = json_decode($data, true);
                endif;
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
