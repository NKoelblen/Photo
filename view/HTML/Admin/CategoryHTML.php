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

    public function category_columns_heads(): string
    {
        ob_start(); ?>

        <th scope="col"></th>
        <th scope="col" class="w-100 px-3" colspan="2">Titre</th>

        <?php return ob_get_clean();
    }

    /**
     * @param CategoryEntity $post
     */
    public function columns(object $post): string
    {
        ob_start(); ?>

        <td class="text-center">
            <?= $post->get_private() ? '<i class="bi bi-lock-fill" style="color: #dc3545"></i>' : ''; ?>
        </td>
        <td class="px-3">
            <span><?= str_repeat('– ', $post->get_level()); ?></span>
            <a
                href="<?= $this->router->get_alto_router()->generate("$this->controller-show", ['slug' => $post->get_slug()]) ?>">
                <?= $post->get_title(); ?>
            </a>
        </td>

        <?php return ob_get_clean();
    }
}