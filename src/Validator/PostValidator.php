<?php

namespace App\Validator;

use App\Repository\PostRepository;

abstract class PostValidator extends AbstractValidator
{
    public function __construct(array $data, PostRepository $table, ?int $id = null)
    {
        parent::__construct($data);
        $this->validator->rule('required', ['title']);
        $this->validator->rule('lengthBetween', 'title', 3, 250);
        if (!str_contains($_SERVER['REQUEST_URI'], 'edit')):
            $this->validator->rule(function ($field, $value) use ($table, $id) {
                return !$table->exist($field, $value, $id);
            }, ['title'], 'est déjà utilisé.');
        endif;
    }
}