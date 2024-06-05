<?php
namespace App\Entity;

use DateTime;

final class PhotoEntity extends PostEntity
{
    private string $path = '';
    private string $description = '';
    private string $created_at = '';
    private ?int $album_id = null;
    private ?int $location_id = null;
    private array $categories_ids = [];

    public function get_path(): string
    {
        return $this->path;
    }
    public function set_path(string $path): static
    {
        $this->path = $path;
        return $this;
    }

    public function get_description(): string
    {
        return nl2br($this->description);
    }
    public function set_description(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function get_created_at(): DateTime
    {
        return new DateTime($this->created_at);
    }
    public function set_created_at(string $created_at): static
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function get_album_id(): ?int
    {
        return $this->album_id;
    }
    public function set_album_id(?int $album_id): static
    {
        $this->album_id = $album_id;
        return $this;
    }

    public function get_location_id(): ?int
    {
        return $this->location_id;
    }
    public function location_id(?int $location_id): static
    {
        $this->location_id = $location_id;
        return $this;
    }

    public function get_categories_ids(): array
    {
        return $this->categories_ids;
    }
    public function set_categories_ids(array $categories_ids): static
    {
        $this->categories_ids = $categories_ids;
        return $this;
    }
}
