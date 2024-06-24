<?php
namespace App\HTML;

use App\Entity\RecursiveEntity;

class RecursiveHTML extends CollectionHTML
{
    public function recursive_breadcrumb(RecursiveEntity $post)
    {
        if ($post->get_ascendants()): ?>
            <nav id="location-breadcrumb" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <?php foreach ($post->get_ascendants() as $ascendant): ?>
                        <li class="breadcrumb-item">
                            <a
                                href="<?= $this->router->get_alto_router()->generate("$this->controller-show", ['slug' => $ascendant->get_slug()]); ?>">
                                <?= $ascendant->get_title(); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </nav>
        <?php endif;
    }
    public function card_list_group(RecursiveEntity $post): string
    {
        if ($post->get_children()):
            ob_start(); ?>
            <ul class="list-group list-group-flush">
                <?php $children_nb = count($post->get_children());
                if ($children_nb > 5):
                    for ($i = 0; $i < 4; $i++):
                        $children[] = $post->get_children()[$i];
                    endfor;
                else:
                    $children = $post->get_children();
                endif;
                foreach ($children as $child): ?>
                    <li class="list-group-item position-relative">
                        <a href="<?= $this->router->get_alto_router()->generate("$this->controller-show", ['slug' => $child->get_slug()]); ?>"
                            class="stretched-link"></a>
                        <?= $child->get_title(); ?>
                    </li>
                <?php endforeach;
                if ($children_nb > 5): ?>
                    <li class="list-group-item position-relative">
                        <a href="<?= $this->router->get_alto_router()->generate("$this->controller-show", ['slug' => $post->get_slug()]); ?>"
                            class="stretched-link"></a>
                        ...
                    </li>
                <?php endif; ?>
            </ul>
            <?php return ob_get_clean();
        endif;
        return '';
    }
}