<?php
namespace App\Entity;

use App\Helpers\JsonMapper;


abstract class CollectionEntity extends PostEntity
{
    protected int $private = 0;
    protected ?string $thumbnail = null;

    public function get_private(): int
    {
        return $this->private;
    }
    public function set_private(int $private): static
    {
        $this->private = $private;
        return $this;
    }

    /**
     * @return PhotoEntity
     */
    public function get_thumbnail(): ?object
    {
        if ($this->thumbnail === null):
            return $this->thumbnail;
        endif;
        return JsonMapper::map($this->thumbnail, PhotoEntity::class);
    }

    public function set_thumbnail(null|string|array $thumbnail): static
    {
        if (is_array($thumbnail)):
            $this->thumbnail = json_encode($thumbnail);
        else:
            $this->thumbnail = $thumbnail;
        endif;
        return $this;
    }
}
