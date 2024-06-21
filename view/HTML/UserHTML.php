<?php
namespace App\HTML;

class UserHTML extends AppHTML
{
    public function login(Form $form): string
    {
        ob_start(); ?>
        <form action="<?= $this->router->get_alto_router()->generate('login'); ?>" method="POST" class="mb-3">
            <?= $form->input('text', 'login', 'Identifiant'); ?>
            <?= $form->input('password', 'password', 'Mot de passe'); ?>
            <button type="submit" class="btn btn-primary">Me connecter</button>
        </form>
        <?php return ob_get_clean();
    }
}