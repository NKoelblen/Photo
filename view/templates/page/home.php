<h1 class="my-4"><?= $title ?></h1>

<section class="my-4">
    <h2 class="my-4">Albums</h2>
    <?php
    $posts = $albums;
    $link = $album_link;
    $route = $album_route;
    require dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'templates_parts/collection/index.php'; ?>
</section>

<section class="my-4">
    <h2 class="my-4">Emplacements</h2>
    <?php
    $posts = $locations;
    $link = $location_link;
    $route = $location_route;
    require dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'templates_parts/collection/recursive/index.php';
    require dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'templates_parts/collection/map.php'; ?>
</section>