<?php
namespace App\HTML;

use App\Entity\YearEntity;

class YearHTML extends CollectionHTML
{
    /**
     * @param YearEntity[] $posts
     */
    public function year_index(array $posts): string
    {
        ob_start(); ?>
        <div class="row text-center row-cols-4 g-4 mb-4">
            <?php foreach ($posts as $post): ?>
                <div class="col">
                    <?= $this->year_card($post); ?>
                </div> <!-- .col-md-3 -->
            <?php endforeach; ?>
        </div> <!-- .row -->
        <?php return ob_get_clean();

    }
    public function year_card(YearEntity $post): string
    {
        ob_start(); ?>
        <article class="card h-100 shadow">
            <img src="<?= str_starts_with($post->get_thumbnail()->get_path(), 'http') ? $post->get_thumbnail()->get_path() : $post->get_thumbnail()->get_path('S'); ?>"
                class="card-img-top" alt="<?= $post->get_thumbnail()->get_description(); ?>">
            <div class="card-body position-relative">
                <?php $tag = $_SERVER['REQUEST_URI'] === '/' ? 'h3' : 'h2';
                echo "<$tag class=\"card-title\">{$post->get_title()}</$tag>"; ?>
                <a href="<?= $this->router->get_alto_router()->generate("$this->controller") . "?year={$post->get_title()}" ?>"
                    class="stretched-link"></a>
            </div> <!-- .card-body -->
        </article> <!-- .card -->
        <?php return ob_get_clean();
    }
}