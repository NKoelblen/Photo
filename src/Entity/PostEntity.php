<?php
namespace App\Entity;

use App\Helpers\Text;

abstract class PostEntity extends AppEntity
{
    protected string $title = '';
    protected string $slug = '';
    protected string $status = 'draft';

    public function get_title(): string
    {
        return $this->title;
    }
    public function set_title(string $title): static
    {
        $this->title = $title;
        $this->set_slug($title);
        return $this;
    }

    public function get_slug(): string
    {
        return $this->slug;
    }
    public function set_slug(string $slug): static
    {
        $this->slug = Text::slugify($slug);
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

}
