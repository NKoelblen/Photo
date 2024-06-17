<?php

namespace App\Validator;

use App\Repository\PhotoRepository;

final class PhotoValidator extends PostValidator
{
    public function __construct(array $data, PhotoRepository $table, array $categories, array $albums, array $locations, ?int $id = null)
    {
        parent::__construct($data, $table, $id);
        $this->validator->rule('subset', 'categories', $categories);
        $this->validator->rule('subset', 'albums_ids', $albums);
        $this->validator->rule('subset', 'locations_ids', $locations);
        $this->validator->rule('image', 'image');
    }
}