<?php
use App\HTML\Admin\PhotoHTML;
use App\HTML\Form;

$HTML = new PhotoHTML($router, $table, $labels);

echo $HTML->alerts($errors);

$form = new Form($form_post, $errors);

ob_start();
echo $form->input('url', 'path', 'Chemin', ['readonly'], ['form-control-plaintext px-3']);
echo $form->input('file', 'image', 'Remplacer');
echo $HTML->post_inputs($form);
echo $form->textarea('description', 'Description');
echo $HTML->photo_inputs($form, $locations, $categories, $albums);
$inputs = ob_get_clean(); ?>

<div class="row g-5">
    <div class="col ratio ratio-1x1" style="max-height: 80vh;">
        <img src="<?= str_starts_with($form_post->get_path(), 'http') ? $form_post->get_path() : $form_post->get_path('L'); ?>"
            alt="Aperçu" class="img-fluid img-thumbnail object-fit-contain">
    </div>
    <div class="col overflow-y-auto overflow-x-hidden" style="max-height: 75vh;">
        <?php echo $HTML->head($title);
        echo $HTML->form($form, $inputs); ?>
    </div>
</div>