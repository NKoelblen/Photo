<?php
namespace App\Validator;

use App\Repository\UserRepository;

final class UserValidator extends AbstractValidator
{
    public function __construct(array $datas, UserRepository $repository, array $categories = [], ?int $id = null)
    {
        parent::__construct($datas);

        $this->validator->rule('required', ['login', 'email']);
        $this->validator->rule('lengthBetween', 'login', 3, 250);
        $this->validator->rule(function ($field, $value) use ($repository, $id) {
            return !$repository->exist($field, $value, $id);
        }, ['login'], 'est déjà utilisé.');
        $this->validator->rule('email', 'email');
        if ($id !== null): // profile, edit
            $this->validator->rule('equals', 'confirmation_password', 'new_password');
        else: // new
            $this->validator->rule('required', 'password');
            $this->validator->rule('equals', 'confirmation_password', 'password');
        endif;
        $this->validator->rule('lengthBetween', 'new_password', 4, 250);
        $this->validator->labels(['new_password' => 'Mot de passe']);
        if (!empty($categories)):
            $this->validator->rule('subset', 'categories_ids', $categories);
        endif;
    }
}
