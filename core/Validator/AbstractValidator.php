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

        self::addRule('image', function ($field, $value, array $params, array $fields) {
            if ($value['size'] === 0):
                return true;
            endif;
            $mimes = ['image/jpeg', 'image/png', 'image/webp'];
            $info = $value['type'];
            return in_array($info, $mimes);
        }, "n'est pas une image valide.");
    }
}