<?php

namespace App\Entity;

class AbstractEntity
{
    protected ?int $id = null;

    public function get_id(): ?int
    {
        return $this->id;
    }
    public function set_id(?int $id): self
    {
        $this->id = $id;
        return $this;
    }
}