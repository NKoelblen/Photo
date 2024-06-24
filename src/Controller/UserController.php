<?php
namespace App\Controller;

use App\Entity\UserEntity;
use App\Repository\Exception\NotFoundException;
use App\Repository\UserRepository;

final class UserController extends AppController
{
    public function login()
    {
        $title = 'Se connecter';

        $form_post = new UserEntity;

        $errors = [];
        if (!empty($_POST)):
            $form_post->set_login($_POST['login']);
            $errors['password'] = "L'identifiant ou le mot de passe est invalide.";
            if (!empty($_POST['login']) && !empty($_POST['password'])):
                $repository = new UserRepository();
                try {
                    /**
                     * @var UserEntity
                     */
                    $user = $repository->find_to_login($_POST['login']);
                    if (password_verify($_POST['password'], $user->get_password()) === true):
                        if (session_status() === PHP_SESSION_NONE):
                            session_start();
                        endif;
                        $_SESSION['auth'] = $user->get_id();
                        $_SESSION['role'] = $user->get_role();
                        $_SESSION['allowed'] = $user->get_categories_ids();
                        header("Location: {$this->router->get_alto_router()->generate('admin')}");
                        exit;
                    endif;
                } catch (NotFoundException $exception) {
                }
            endif;
        endif;

        return $this->render(
            view: 'auth/login',
            data: compact('title', 'form_post', 'errors')
        );
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE):
            session_start();
        endif;
        session_destroy();
        header("Location: {$this->router->get_alto_router()->generate('home')}");
        exit();
    }
}