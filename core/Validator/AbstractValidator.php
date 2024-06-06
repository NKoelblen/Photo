<?php

namespace App\Validator;

use Valitron\Validator;

class AbstractValidator extends Validator
{
    protected array $datas;
    protected Validator $validator;
    public function __construct(array $datas)
    {
        $this->datas = $datas;

        Validator::lang('fr');
        $validator = new Validator($datas);

        $this->validator = $validator;
    }
}