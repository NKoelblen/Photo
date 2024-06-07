<?php require dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'templates_parts/head.php';

if ($post->get_ascendants()): ?>
    <nav id="location-breadcrumb" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <?php foreach ($post->get_ascendants() as $ascendant): ?>
                <li class="breadcrumb-item">
                    <a href="<?= $router->get_alto_router()->generate($route, ['slug' => $ascendant->get_slug()]); ?>">
                        <?= $ascendant->get_title(); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ol>
    </nav>
<?php endif;

if ($post->get_children()):
    $posts = $post->get_children();
    $route = 'location';
    require dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'templates_parts/collection/recursive/index.php';
endif;

require dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'templates_parts/collection/map.php';