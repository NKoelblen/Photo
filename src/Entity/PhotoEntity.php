<?php
namespace App\Entity;

use App\Helpers\JsonMapper;
use DateTime;

final class PhotoEntity extends PostEntity
{
    private string $path = '';
    private array $image = [];
    private string $old_image = '';
    private string $description = '';
    private string $created_at = '';
    private ?int $album_id = null;
    private ?string $album = null;
    private ?string $locations_ids = null;
    private ?string $locations = null;
    private ?string $categories_ids = null;
    private ?string $categories = null;

    public function get_path(string $format = ''): string
    {
        return $format ? "$this->path-$format.webp" : $this->path;
    }
    public function set_path(string $path): static
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return array ['name', 'tmp_name']
     */
    public function get_image(): array
    {
        return $this->image;
    }
    /**
     * @param array $image ['name', 'tmp_name']
     */
    public function set_image(array $image): static
    {
        if (!empty($this->path)):
            $this->old_image = $this->path;
        endif;
        $this->image = $image;
        return $this;
    }
    public function get_old_image(): string
    {
        return $this->old_image;
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

    public function get_created_at(): ?DateTime
    {
        if ($this->created_at === ''):
            return null;
        endif;
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

    /**
     * @return ?AlbumEntity
     */
    public function get_album(): ?object
    {
        if ($this->album === null):
            return $this->album;
        endif;
        return JsonMapper::map($this->album, AlbumEntity::class);
    }

    public function set_album(?string $album): static
    {
        if (is_array($album)):
            $this->album = json_encode($album);
        else:
            $this->album = $album;
        endif;
        return $this;
    }
    public function get_locations_ids(): ?array
    {
        if ($this->locations_ids === null):
            return $this->locations_ids;
        endif;
        return json_decode($this->locations_ids, true);
    }
    public function set_locations_ids(?array $locations_ids): static
    {
        if (is_array($locations_ids)):
            $this->locations_ids = json_encode($locations_ids);
        else:
            $this->locations_ids = $locations_ids;
        endif;
        return $this;
    }
    /**
     * @return ?LocationEntity[]
     */
    public function get_locations(): ?array
    {
        if ($this->locations === null):
            return $this->locations;
        endif;
        return JsonMapper::map_array($this->locations, LocationEntity::class);
    }

    public function set_locations(?string $locations): static
    {
        if (is_array($locations)):
            $this->locations = json_encode($locations);
        else:
            $this->locations = $locations;
        endif;
        return $this;
    }

    public function get_categories_ids(): ?array
    {
        if ($this->categories_ids === null):
            return $this->categories_ids;
        endif;
        return json_decode($this->categories_ids, true);
    }
    public function set_categories_ids(?array $categories_ids): static
    {
        if (is_array($categories_ids)):
            $this->children_ids = json_encode($categories_ids);
        else:
            $this->children_ids = $categories_ids;
        endif;
        return $this;
    }
    /**
     * @return ?CategoryEntity[]
     */
    public function get_categories(): ?array
    {
        if ($this->categories === null):
            return $this->categories;
        endif;
        return JsonMapper::map_array($this->categories, CategoryEntity::class);
    }

    public function set_categories(?string $categories): static
    {
        if (is_array($categories)):
            $this->categories = json_encode($categories);
        else:
            $this->categories = $categories;
        endif;
        return $this;
    }
}
