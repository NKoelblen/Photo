<?php
namespace App\Entity;

use App\Helpers\Json;
use App\Helpers\JsonMapper;

abstract class RecursiveEntity extends PostEntity
{
    protected ?int $parent_id = null;
    protected ?string $children_ids = null;
    protected ?string $children = null;
    protected ?string $ascendants = null;
    protected string $path = '';
    protected int $level = 0;

    public function __construct()
    {
        $this->path = $this->title;
    }

    public function get_parent_id(): ?int
    {
        return $this->parent_id;
    }
    public function set_parent_id(int $parent_id): static
    {
        $this->parent_id = $parent_id;
        return $this;
    }
    public function get_children_ids(): ?array
    {
        if ($this->children_ids === null):
            return $this->children_ids;
        endif;
        return json_decode($this->children_ids, true);
    }

    public function set_children_ids(?array $children_ids): static
    {
        if (is_array($children_ids)):
            $this->children_ids = json_encode($children_ids);
        else:
            $this->children_ids = $children_ids;
        endif;
        return $this;
    }

    /**
     * @return ?RecursiveEntity[]
     */
    public function get_children(): ?array
    {
        if ($this->children === null):
            return $this->children;
        endif;
        return JsonMapper::map_array($this->children, $this::class);
    }

    public function set_children(?string $children): static
    {
        if (is_array($children)):
            $this->children = json_encode($children);
        else:
            $this->children = $children;
        endif;
        return $this;
    }

    /**
     * @return ?RecursiveEntity[]
     */
    public function get_ascendants(): ?array
    {
        if ($this->ascendants === null):
            return $this->ascendants;
        endif;
        return JsonMapper::map_array($this->ascendants, $this::class);
    }

    public function set_ascendants(?string $ascendants): static
    {
        if (is_array($ascendants)):
            $this->ascendants = json_encode($ascendants);
        else:
            $this->ascendants = $ascendants;
        endif;
        return $this;
    }

    public function get_path(): string
    {
        return $this->path;
    }

    public function get_level(): int
    {
        return $this->level;
    }
}
