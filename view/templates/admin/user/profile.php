<?php
use App\HTML\Admin\UserHTML;
use App\HTML\Form;

$HTML = new UserHTML($router, $table, $labels);

// echo $HTML->alerts($errors);
echo $HTML->head($title);

// $form = new Form($form_post, $errors);

// ob_start();
/**
 * LOGIN, EMAIL, OLD PASSWORD, NEW PASSWORD & CONFIRMATION PASSWORD INPUTS
 */
// $inputs = ob_get_clean();

// echo $HTML->form($form, $success, $inputs);