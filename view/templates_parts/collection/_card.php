<article class="card position-relative h-100 shadow">
    <div class="card-body">
        <?php $tag = $_SERVER['REQUEST_URI'] === '/' ? 'h3' : 'h2';
        echo "<$tag class=\"card-title\">{$post->get_title()}</$tag>"; ?>
    </div> <!-- .card-body -->
    <a href="<?= $router->get_alto_router()->generate($route, ['slug' => $post->get_slug()]) ?>"
        class="stretched-link"></a>
</article> <!-- .card -->