<?php
namespace App\HTML\Admin;

use App\Entity\LocationEntity;

class LocationHTML extends RecursiveHTML
{
    /**
     * @param LocationEntity $post
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
        <td class="px-3 text-end">
            <?= isset($post->get_photos_nb()['public']) && $post->get_photos_nb()['public'] ? $post->get_photos_nb()['public'] : '' ?>
        </td>
        <td class="px-3 text-end">
            <?= isset($post->get_photos_nb()['private']) && $post->get_photos_nb()['private'] ? $post->get_photos_nb()['private'] : '' ?>
        </td>

        <?php return ob_get_clean();
    }
}