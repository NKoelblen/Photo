<?php
namespace App\HTML\Admin;

use App\Entity\RecursiveEntity;
use App\HTML\Form;

class RecursiveHTML extends CollectionHTML
{
    public function recursive_inputs(Form $form, array $options): string
    {
        ob_start(); ?>

        <div id="parent-children" class="row w-100 g-3">

            <div class="col">
                <?= $form->parent_radio('parent_id', 'Parent', $options); ?>
            </div>

            <div class="col">
                <?= $form->children_checkbox('children_ids', 'Enfants', $options); ?>
            </div>

        </div>

        <?php return ob_get_clean();
    }

    /**
     * @param RecursiveEntity $post
     */
    public function columns(object $post): string
    {
        ob_start(); ?>

        <td class="px-3">
            <span><?= str_repeat('– ', $post->get_level()); ?></span>
            <a
                href="<?= $this->router->get_alto_router()->generate("$this->controller-show", ['slug' => $post->get_slug()]) ?>">
                <?= $post->get_title(); ?>
            </a>
        </td>
        <td class="text-center px-3">
            <?= $post->get_private() ? '<i class="bi bi-lock-fill" style="color: #dc3545"></i>' : ''; ?></td>

        <?php return ob_get_clean();
    }
}