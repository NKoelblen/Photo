<?php
namespace App\HTML;

use App\Entity\CollectionEntity;
use App\Entity\PostEntity;

class CollectionHTML extends PostHTML
{
    /**
     * @param PostEntity[] $posts
     */
    public function collection_index(array $posts): string
    {
        ob_start(); ?>
        <div class="row text-center row-cols-4 g-4 mb-4">
            <?php foreach ($posts as $post): ?>
                <div class="col">
                    <?= $this->collection_card($post); ?>
                </div> <!-- .col-md-3 -->
            <?php endforeach; ?>
        </div> <!-- .row -->
        <?php return ob_get_clean();

    }
    public function collection_card(CollectionEntity $post): string
    {
        ob_start(); ?>
        <article class="card h-100 shadow">
            <img src="<?= str_starts_with($post->get_thumbnail()->get_path(), 'http') ? $post->get_thumbnail()->get_path() : $post->get_thumbnail()->get_path('S'); ?>"
                class="card-img-top" alt="<?= $post->get_thumbnail()->get_description(); ?>">
            <div class="card-body position-relative">
                <?php $tag = $_SERVER['REQUEST_URI'] === '/' ? 'h3' : 'h2';
                echo "<$tag class=\"card-title\">{$post->get_title()}</$tag>"; ?>
                <a href="<?= $this->router->get_alto_router()->generate("$this->controller-show", ['slug' => $post->get_slug()]) ?>"
                    class="stretched-link"></a>
            </div> <!-- .card-body -->
            <?= method_exists($this::class, 'card_list_group') ? $this->card_list_group($post) : ''; ?>
            <?= method_exists($this::class, 'card_footer') ? $this->card_footer($post) : ''; ?>
        </article> <!-- .card -->
        <?php return ob_get_clean();
    }
}