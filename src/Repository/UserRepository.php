<?php
namespace App\Repository;

use App\Entity\UserEntity;

final class UserRepository extends AppRepository
{
    protected ?string $table = 'user';
    protected ?string $entity = UserEntity::class;

    /********** ADMIN **********/
    public function create_user(UserEntity $user, array $categories_ids): int
    {
        $new_user = $this->create(
            [
                'login' => $user->get_login(),
                'email' => $user->get_email(),
                'password' => password_hash($user->get_password(), PASSWORD_BCRYPT),
                'role' => $user->get_role()
            ]
        );

        $this->insert_categories(
            user_id: $new_user,
            categories_ids: $categories_ids
        );

        return $this->pdo->lastInsertId();
    }

    public function edit_user(UserEntity $user, array $datas, array $categories_ids): void
    {
        $id = $user->get_id();

        $this->update(
            ids: [$id],
            datas: $datas
        );

        $this->remove_categories($id);
        $this->insert_categories(
            user_id: $id,
            categories_ids: $categories_ids
        );
    }

    protected function insert_categories(int $user_id, ?array $categories_ids): void
    {
        if ($categories_ids === null):
            return;
        endif;

        $category_list = implode($categories_ids);

        $params = [];
        $values = [];
        $i = 0;
        foreach ($categories_ids as $category_id):
            $user_key = "{$this->table}_id$i";
            $category_key = "category_id$i";
            $values[] = "(:$user_key, :$category_key)";
            $params[$user_key] = $user_id;
            $params[$category_key] = $category_id;
            $i++;
        endforeach;
        $values = implode(', ', $values);

        $this->execute_query(
            sql_query:
            "INSERT INTO nk_{$this->table}_category ({$this->table}_id, category_id)
                 VALUES $values
                 ON DUPLICATE KEY UPDATE {$this->table}_id = {$this->table}_id, category_id = category_id",
            params: $params,
            method: null,
            message: "Impossible d'attacher les publications $category_list dans la table 'category' à la publications $user_id dans la table $this->table."
        );
    }

    protected function remove_categories(int $user_id): void
    {
        $this->execute_query(
            sql_query:
            "DELETE FROM nk_{$this->table}_category 
             WHERE {$this->table}_id = :user_id",
            params: ['user_id' => $user_id],
            method: null,
            message: "Impossible d'attacher les publications de la table 'category' à la publication $user_id dans la table $this->table."
        );
    }

    public function find_to_login(string $login): UserEntity
    {
        return $this->fetch_entity(
            sql_query:
            "SELECT
                 id,
                 login, 
                 email, 
                 password, 
                 role
             FROM nk_$this->table t
             WHERE login = :login",

            params: compact('login')
        );
    }

    public function find_profile(int $id): UserEntity
    {
        return $this->fetch_entity(
            sql_query:
            "SELECT login, email, password
             FROM nk_$this->table t
             WHERE id = :id",

            params: compact('id')
        );
    }

    public function find_to_edit(int $id): UserEntity
    {
        return $this->fetch_entity(
            sql_query:
            "SELECT 
                 login, 
                 email, 
                 password, 
                 role, 
                 label AS role_label
                 JSON_ARRAYAGG(tc.category_id) AS categories_ids
             FROM nk_$this->table t
             JOIN nk_role r ON t.role = r.id
             LEFT JOIN nk_{$this->table}_category tc ON t.id = tc.{$this->table}_id
             WHERE id = :id
             GROUP BY id",

            params: compact('id')
        );
    }

    /**
     * @return UserEntity[]
     */
    public function find_all(): array
    {
        return $this->fetch_entities(
            "SELECT id, login, email, label AS role_label
             FROM nk_$this->table t
             JOIN nk_role r ON t.role = r.id"
        );
    }
}
