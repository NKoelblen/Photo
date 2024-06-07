<?php
namespace App\Entity;

final class LocationEntity extends RecursiveEntity
{
    private ?string $coordinates = null;

    public function get_coordinates(): ?string
    {
        return $this->coordinates;
    }
    public function set_coordinates(?string $coordinates): static
    {
        $this->coordinates = $coordinates;
        return $this;
    }
}
