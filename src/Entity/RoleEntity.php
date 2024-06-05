<?php
namespace App\Entity;

final class RoleEntity
{
    private string $id = '';
    private string $label = '';
    private string $permissions = '';

    public function get_id(): string
    {
        return $this->id;
    }

    public function get_label(): string
    {
        return $this->label;
    }
    public function set_label($label): static
    {
        $this->label = $label;
        return $this;
    }

    public function get_permissions(): array
    {
        return json_decode($this->permissions, true);
    }
    public function set_permissions(array $permissions): static
    {
        $this->permissions = json_encode($permissions);
        return $this;
    }
}