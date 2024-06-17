<?php
namespace App\Entity;

use DateTime;

final class AlbumEntity extends CollectionEntity
{
    protected ?string $private_ids = null;
    private string $date_from = '';
    private string $date_to = '';

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

    public function get_date_from(): ?DateTime
    {
        if ($this->date_from === ''):
            return null;
        endif;
        return new DateTime($this->date_from);
    }
    public function set_date_from(string $date_from): static
    {
        $this->date_from = $date_from;
        return $this;
    }

    public function get_date_to(): ?DateTime
    {
        if ($this->date_to === ''):
            return null;
        endif;
        return new DateTime($this->date_to);
    }
    public function set_date_to(string $date_to): static
    {
        $this->date_to = $date_to;
        return $this;
    }
}
