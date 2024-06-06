<?php
namespace App;

use Exception;

class URL
{

    public static function get_int(string $name, int $default = null): ?int
    {
        if (!isset($_GET[$name])):
            return $default;
        endif;
        if ($_GET[$name] === '0'):
            return 0;
        endif;
        if (!filter_var($_GET[$name], FILTER_VALIDATE_INT)):
            throw new Exception("Le paramètre $name dans l'url n'est pas un entier.");
        endif;
        return (int) $_GET[$name];
    }

    public static function get_positif_int(string $name, int $default = null): ?int
    {
        $param = self::get_int($name, $default);
        if ($param !== null && $param <= 0):
            throw new Exception("Le paramètre $name dans l'url n'est pas un entier positif.");
        endif;
        return $param;
    }

}