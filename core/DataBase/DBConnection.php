<?php
namespace App\DataBase;

use PDO;

class DBConnection
{
    public static function get_pdo(): PDO
    {
        return new PDO('mysql:dbname=' . DB_NAME . ';host=' . DB_HOST, DB_USER, DB_PASSWORD, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

    }
}