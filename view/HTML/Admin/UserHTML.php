<?php
namespace App\HTML\Admin;

use App\Entity\UserEntity;
use App\HTML\Form;
use App\Repository\Pagination;

class UserHTML extends AdminHTML
{
    public function user_index(array $posts, Pagination $pagination, string $link)
    {
        ob_start(); ?>

        <table id="index" class="table table-sm table-striped table-hover align-middle mb-4">

            <thead>
                <tr>
                    <th scope="col" class="text-end px-3">#</th>

                    <th scope="col" class="px-3">Identifiant</th>
                    <th scope="col" class="px-3">Email</th>
                    <th scope="col" class="px-3">Rôle</th>
                    <th scope="col" class="px-3 w-100">Permissions</th>
                    <th scope="col" class="px-3">
                        <a href="<?= $this->router->get_alto_router()->generate('admin-user-new'); ?>" class="btn btn-success">
                            <i class="bi bi-file-earmark-plus"></i>
                        </a>
                    </th>
                </tr>
            </thead>

            <tbody class="table-group-divider">
                <?php foreach ($posts as $post): ?>
                    <tr>
                        <td class="text-end px-3"><?= $post->get_id(); ?></td>
                        <td class="px-3"><?= $post->get_login(); ?></td>
                        <td class="px-3"><?= $post->get_email(); ?></td>
                        <td class="px-3"><?= $post->get_role_label(); ?></td>
                        <td class="px-3">
                            <?php $categories_details = $post->get_categories();
                            $categories = [];
                            if ($categories_details):
                                foreach ($categories_details as $category):
                                    ob_start(); ?>
                                    <a
                                        href="<?= $this->router->get_alto_router()->generate('admin-category-edit', ['id' => $category->get_id()]); ?>">
                                        <?= $category->get_title(); ?>
                                    </a>
                                    <?php $categories[] = ob_get_clean();
                                endforeach;
                            endif;
                            echo implode(' | ', $categories); ?>
                        </td>
                        <td class="px-3" style="white-space: nowrap;">
                            <?php $route = $_SESSION['auth'] === $post->get_id() ? 'profile' : 'admin-user-edit' ?>
                            <a href="<?= $this->router->get_alto_router()->generate($route, ['id' => $post->get_id()]) ?>"
                                class="btn btn-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form
                                action="<?= $this->router->get_alto_router()->generate('admin-user-delete', ['id' => $post->get_id()]); ?>"
                                method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer cet utilisateur ?')"
                                class="d-inline-block">
                                <button type="submit" class="btn btn-danger"><i class="bi bi-trash3"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?= $this->pagination($pagination, $link);

        return ob_get_clean();
    }

    public function form(Form $form, string $inputs): string
    {
        ob_start(); ?>

        <!-- Form -->
        <form action="" method="POST" class="my-4" enctype="multipart/form-data">

            <?= $inputs; ?>

            <button type="submit"
                class="btn <?= str_contains($_SERVER['REQUEST_URI'], "$this->controller/edit") || $_SERVER['REQUEST_URI'] === "/profile" ? 'btn-primary' : 'btn-success'; ?>">
                <i
                    class="bi <?= str_contains($_SERVER['REQUEST_URI'], "$this->controller/edit") || $_SERVER['REQUEST_URI'] === "/profile" ? 'bi-floppy' : 'bi-file-earmark-plus'; ?>">
                </i>
            </button>
        </form>

        <?php return ob_get_clean();
    }

    public function profile_inputs(Form $form): string
    {
        ob_start(); ?>
        <div class="row g-4">
            <div class="col">
                <?= $form->input('text', 'login', 'Identifiant'); ?>
                <?= $form->input('email', 'email', 'Email'); ?>
                <?= $form->input('password', 'password', '', ['hidden']); ?>
            </div>
            <div class="col">
                <?= $form->input('password', 'current_password', 'Mot de passe actuel'); ?>
                <?= $form->input('password', 'new_password', 'Nouveau mot de passe'); ?>
                <?= $form->input('password', 'confirmation_password', 'Confirmer le mot de passe'); ?>
            </div>
        </div>
        <?php return ob_get_clean();
    }

    public function edit_inputs(Form $form, array $categories): string
    {
        ob_start(); ?>
        <div class="row g-4">
            <div class="col">
                <?= $form->input('text', 'login', 'Identifiant'); ?>
                <?= $form->input('email', 'email', 'Email'); ?>
                <?= $form->input('password', 'new_password', 'Nouveau mot de passe'); ?>
                <?= $form->input('password', 'confirmation_password', 'Confirmer le mot de passe'); ?>
                <?= $form->select('role', 'Rôle', ['subscriber' => 'Abonné', 'admin' => 'Administrateur']); ?>
            </div>
            <div class="col">
                <?= $form->checkbox('categories_ids', 'Permissions', $categories); ?>
            </div>
        </div>
        <?php return ob_get_clean();
    }

    public function new_inputs(Form $form, array $categories): string
    {
        ob_start(); ?>
        <div class="row g-4">
            <div class="col">
                <?= $form->input('text', 'login', 'Identifiant'); ?>
                <?= $form->input('email', 'email', 'Email'); ?>
                <?= $form->input('password', 'password', 'Mot de passe'); ?>
                <?= $form->input('password', 'confirmation_password', 'Confirmer le mot de passe'); ?>
                <?= $form->select('role', 'Rôle', ['subscriber' => 'Abonné', 'admin' => 'Administrateur']); ?>
            </div>
            <div class="col">
                <?= $form->checkbox('categories_ids', 'Permissions', $categories); ?>
            </div>
        </div>
        <?php return ob_get_clean();
    }
}