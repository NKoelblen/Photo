<?php

namespace App\Validator;

use App\Repository\LocationRepository;

final class LocationValidator extends PostValidator
{
    public function __construct(array $data, array $list, LocationRepository $table, ?int $id = null)
    {
        parent::__construct($data, $table, $id);
        if (!str_contains($_SERVER['REQUEST_URI'], 'edit')):
            $this->validator->rule(function ($field, $value) use ($table, $id) {
                return !$table->exist($field, $value, $id);
            }, ['title', 'coordinates'], 'est déjà utilisé.');
        endif;
        $this->validator->rule('subset', 'parent_id', $list);
        $this->validator->rule('subset', 'children_ids', $list);
    }
}