<?php
namespace App\Entity;

abstract class RecursiveEntity extends PostEntity
{
    private ?int $parent_id = null;

    public function get_parent_id(): ?int
    {
        return $this->parent_id;
    }
    public function set_parent_id(int $parent_id): static
    {
        $this->parent_id = $parent_id;
        return $this;
    }
}
