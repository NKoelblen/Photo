<?php
use App\HTML\AlbumHTML;
use App\HTML\AppHTML;
use App\HTML\CategoryHTML;
use App\HTML\LocationHTML;
use App\HTML\PhotoHTML;
use App\HTML\YearHTML;

$HTML = new AppHTML($router);
echo $HTML->head($title); ?>

<section class="my-5">
    <h2 class="my-4">Albums</h2>
    <?php $album_HTML = new AlbumHTML($router, $album_controller);
    echo $album_HTML->collection_index($albums); ?>
</section>

<section class="my-5">
    <h2 class="my-4">Emplacements</h2>
    <?php $location_HTML = new LocationHTML($router, $location_controller);
    echo $location_HTML->collection_index($locations);
    echo $location_HTML->map(); ?>
</section>

<section class="my-5">
    <h2 class="my-4">Catégories</h2>
    <?php $category_HTML = new CategoryHTML($router, $category_controller);
    echo $category_HTML->collection_index($categories); ?>
</section>

<section class="my-5">
    <h2 class="my-4">Années</h2>
    <?php
    $year_HTML = new YearHTML($router, $years_controller);
    echo $year_HTML->year_index($years); ?>
</section>