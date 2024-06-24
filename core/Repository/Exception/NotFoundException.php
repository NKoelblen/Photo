<?php
namespace App\Repository\Exception;

use Exception;

class NotFoundException extends Exception
{
    public function __construct(string $table, mixed $field = '', string $message = '')
    {
        $this->message = ($message ?: "Aucun enregistrement ne correspond à $field") . " dans la table $table";
    }
}