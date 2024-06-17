<?php
namespace App\Entity;

final class LocationEntity extends RecursiveEntity
{
    protected ?string $private_ids = null;
    private ?string $coordinates = null;

    public function get_private_ids(): ?array
    {
        if (is_string($this->private_ids)):
            return json_decode($this->private_ids, true);
        else:
            return $this->private_ids;
        endif;
    }
    public function set_private_ids(?array $private_ids): static
    {
        if (is_array($private_ids)):
            $this->private_ids = json_encode($private_ids);
        else:
            $this->private_ids = $private_ids;
        endif;
        return $this;
    }

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
