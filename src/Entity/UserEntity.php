<?php
namespace App\Entity;

final class UserEntity extends AppEntity
{
    private string $login = '';
    private string $email = '';
    private string $password = '';
    private string $role = 'subscriber';
    private array $categories_ids = [];

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

    public function get_role(): string
    {
        return $this->role;
    }
    public function set_role(string $role): static
    {
        $this->role = $role;
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