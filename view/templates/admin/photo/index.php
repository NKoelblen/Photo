<?php
use App\HTML\Admin\PhotoHTML;

$HTML = new PhotoHTML($router, $table, $labels);

echo $HTML->alerts();
echo $HTML->head($title);

echo $HTML->bulk_create_photo();

echo $HTML->filter($locations_filter, $categories_filter, $albums_list);

ob_start();
echo $HTML->photo_columns_heads();
$columns_heads = ob_get_clean();

ob_start();
echo $HTML->photo_tfoot($status, $locations_list, $categories_list, $albums_list);
$tfoot = ob_get_clean();

echo $HTML->index($posts, $pagination, $link, $columns_heads, $tfoot, $status, $status_count);