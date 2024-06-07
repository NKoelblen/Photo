<?php

namespace App\Validator;

use App\Repository\PostRepository;
use App\Repository\RecursiveRepository;

abstract class RecursiveValidator extends PostValidator
{
    public function __construct(array $data, array $list, RecursiveRepository $table, ?int $id = null)
    {
        parent::__construct($data, $table, $id);
        $this->validator->rule('subset', 'parent_id', $list);
        $this->validator->rule('subset', 'children_ids', $list);
    }
}