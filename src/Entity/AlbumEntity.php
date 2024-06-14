<?php
namespace App\Entity;

use DateTime;

final class AlbumEntity extends CollectionEntity
{
    private string $date_from = '';
    private string $date_to = '';

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
