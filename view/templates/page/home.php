<h1 class="my-4"><?= $title ?></h1>

<section class="my-4">
    <h2 class="my-4">Albums</h2>
    <?php
    $posts = $albums;
    require dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'templates_parts/collection/index.php'; ?>
</section>