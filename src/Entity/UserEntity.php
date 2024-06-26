<?php
namespace App\Entity;

use App\Helpers\JsonMapper;

final class UserEntity extends AppEntity
{
    private string $login = '';
    private string $email = '';
    private string $password = '';
    private string $role = 'subscriber';
    private string $role_label = '';
    private string $permissions = '';
    private ?string $categories_ids = null;
    private ?string $categories = null;

    public function get_login(): string
    {
        return $this->login;
    }
    public function set_login(string $login): static
    {
        $this->login = $login;
        return $this;
    }

    public function get_email(): string
    {
        return $this->email;
    }
    public function set_email(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function get_password(): string
    {
        return $this->password;
    }
    public function set_password(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function set_new_password(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function get_role(): string
    {
        return $this->role;
    }
    public function set_role(string $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function get_role_label(): string
    {
        return $this->role_label;
    }
    public function set_role_label(string $role_label): static
    {
        $this->role_label = $role_label;
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
            $this->categories_ids = json_encode($categories_ids);
        else:
            $this->categories_ids = $categories_ids;
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
    public function set_categories(string|array|null $categories): static
    {
        if (is_array($categories)):
            $this->categories = json_encode($categories);
        else:
            $this->categories = $categories;
        endif;
        return $this;
    }
}