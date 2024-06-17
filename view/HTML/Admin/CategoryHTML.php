<?php
namespace App\HTML\Admin;

use App\Entity\CategoryEntity;
use App\HTML\Form;

class CategoryHTML extends RecursiveHTML
{
    public function category_inputs(Form $form, array $options): string
    {
        ob_start();
        echo $form->checkbox('private', '', [true => 'Privée'], ['role="switch"'], ['form-switch']); ?>
        <div id="parent-children" class="row w-100 g-3">

            <div class="col">
                <?= $form->parent_radio('parent', 'Parent', $options); ?>
            </div>

            <div class="col">
                <?= $form->children_checkbox('children', 'Enfants', $options); ?>
            </div>

        </div>
        <?php return ob_get_clean();
    }
}