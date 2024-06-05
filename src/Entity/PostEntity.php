<?php
namespace App\Entity;

abstract class PostEntity extends AppEntity
{
    protected string $title = '';
    protected string $slug = '';
    protected string $status = 'published';
    protected bool $private = false;

    public function get_title(): string
    {
        return $this->title;
    }
    public function set_title(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function get_slug(): string
    {
        return $this->slug;
    }
    public function set_slug(string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    public function get_status(): string
    {
        return $this->status;
    }
    public function set_status(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function get_private(): bool
    {
        return $this->private;
    }
    public function set_private(bool $private): static
    {
        $this->private = $private;
        return $this;
    }
}
