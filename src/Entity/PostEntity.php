<?php
namespace App\Entity;

use App\Helpers\Text;

abstract class PostEntity extends AppEntity
{
    protected string $title = '';
    protected string $slug = '';
    protected string $status = 'published';
    protected string|int|null $private = null;

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

    public function get_private(): array|bool|null
    {
        if (is_string($this->private)):
            return json_decode($this->private, true);
        elseif (is_int($this->private)):
            return (bool) $this->private;
        else:
            return $this->private;
        endif;
    }
    public function set_private(array|bool|null $private): static
    {
        if (is_array($private)):
            $this->private = json_encode($private);
        elseif (is_bool($private)):
            $this->private = (int) $private;
        else:
            $this->private = $private;
        endif;
        return $this;
    }
}
