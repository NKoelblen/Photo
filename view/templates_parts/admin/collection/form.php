<?php
use App\Helpers\Form;

$form = new Form($form_post, $errors); ?>

<!-- Alerts -->
<?php if ($success): ?>
    <div class="alert alert-success">La publication a bien été enregistrée.</div>
<?php elseif (!empty($errors)): ?>
    <div class="alert alert-danger">La publication n'a pas pu être enregistrée. Merci de corriger les champs erronés.</div>
<?php endif; ?>
<?php if (isset($_GET['trash'])): ?>
    <div class="alert alert-success">
        <?php if ($_GET['trash'] === '1'): ?>
            La publication a bien été mise à la corbeille.
        <?php elseif ($_GET['trash'] === '2'): ?>
            Les publications ont bien été mises à la corbeille.
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php if (isset($_GET['restore'])): ?>
    <div class="alert alert-success">
        <?php if ($_GET['restore'] === '1'): ?>
            La publication a bien été restaurée.
        <?php elseif ($_GET['restore'] === '2'): ?>
            Les publications ont bien été restaurées.
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php if (isset($_GET['delete'])): ?>
    <div class="alert alert-success">
        <?php if ($_GET['delete'] === '1'): ?>
            La publication a bien été supprimée.
        <?php elseif ($_GET['delete'] === '2'): ?>
            Les publications ont bien été supprimées.
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Form -->
<form action="" method="POST" class="my-4" enctype="multipart/form-data">
    <?= $form->input('text', 'title', 'Titre'); ?>
    <button type="submit"
        class="btn <?= str_contains($_SERVER['REQUEST_URI'], "{$route['plural']}/") && !str_contains($_SERVER['REQUEST_URI'], '/trash') ? 'btn-primary' : 'btn-success'; ?>">
        <i
            class="bi <?= str_contains($_SERVER['REQUEST_URI'], "{$route['plural']}/") && !str_contains($_SERVER['REQUEST_URI'], '/trash') ? 'bi-floppy' : 'bi-file-earmark-plus'; ?>">
        </i>
    </button>
</form>