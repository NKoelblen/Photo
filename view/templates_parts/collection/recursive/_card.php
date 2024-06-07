<article class="card h-100 shadow">
    <div class="card-body position-relative ">
        <?php $tag = $_SERVER['REQUEST_URI'] === '/' ? 'h3' : 'h2';
        echo "<$tag class=\"card-title\">{$post->get_title()}</$tag>"; ?>
        <a href="<?= $router->get_alto_router()->generate($route, ['slug' => $post->get_slug()]) ?>"
            class="stretched-link"></a>
    </div> <!-- .card-body -->
    <?php if ($post->get_children()): ?>
        <ul class="list-group list-group-flush">
            <?php foreach ($post->get_children() as $child): ?>
                <li class="list-group-item position-relative">
                    <a href="<?= $router->get_alto_router()->generate($route, ['slug' => $child->get_slug()]); ?>"
                        class="stretched-link"></a>
                    <?= $child->get_title(); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</article> <!-- .card -->