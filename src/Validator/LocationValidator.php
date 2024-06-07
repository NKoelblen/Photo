<?php

namespace App\Validator;

use App\Repository\LocationRepository;

final class LocationValidator extends RecursiveValidator
{
    public function __construct(array $data, array $list, LocationRepository $table, ?int $id = null)
    {
        parent::__construct($data, $list, $table, $id);
        $this->validator->rule(function ($field, $value) use ($table, $id) {
            return !$table->exist($field, $value, $id);
        }, ['title', 'coordinates'], 'est déjà utilisé.');
    }
}