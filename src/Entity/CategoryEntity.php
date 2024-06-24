<?php
namespace App\Entity;

final class CategoryEntity extends RecursiveEntity
{
    private int $private = 0;

    public function get_private(): int
    {
        return $this->private;
    }
    public function set_private(int $private): static
    {
        $this->private = $private;
        return $this;
    }
}