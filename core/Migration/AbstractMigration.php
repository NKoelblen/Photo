<?php
namespace App\Migration;

use App\DataBase\DBConnection;
use PDO;

abstract class AbstractMigration
{
    protected static string $query = '';

    protected static function get_pdo()
    {
        return DBConnection::get_pdo();
    }

    public static function up(): void
    {
        $pdo = self::get_pdo();
        $pdo->query(static::$query);
    }
}