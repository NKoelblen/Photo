<?php
use App\HTML\Admin\PhotoHTML;

$HTML = new PhotoHTML($router, $table, $labels);

echo $HTML->alerts();
echo $HTML->head($title);

echo $HTML->bulk_create_photo();

echo $HTML->filter($filter_locations, $filter_categories, $filter_album);

ob_start();
echo $HTML->photo_columns_heads();
$columns_heads = ob_get_clean();

ob_start();
echo $HTML->photo_tfoot($status, $filter_locations, $filter_categories, $filter_album);
$tfoot = ob_get_clean();

echo $HTML->index($posts, $pagination, $link, $columns_heads, $tfoot, $status, $status_count);