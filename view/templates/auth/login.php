<?php
use App\HTML\Form;
use App\HTML\UserHTML;

$user = new UserHTML($router);
echo $user->head($title);
$form = new Form($form_post, $errors);
echo $user->login($form);