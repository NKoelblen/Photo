<?php

namespace App\Validator;

use App\Repository\CategoryRepository;

final class CategoryValidator extends PostValidator
{
    public function __construct(array $data, array $list, CategoryRepository $table, ?int $id = null)
    {
        parent::__construct($data, $table, $id);
        $this->validator->rule('subset', 'parent', $list);
        $this->validator->rule('subset', 'children', $list);
    }
}