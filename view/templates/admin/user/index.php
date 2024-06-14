<?php
use App\HTML\Admin\UserHTML;

$HTML = new UserHTML($router, $table, $labels);

// echo $HTML->alerts();
echo $HTML->head($title);

// ob_start();
// echo $HTML->post_columns_heads();
// $columns_heads = ob_get_clean();

// ob_start();
// echo $HTML->post_tfoot($status);
// $tfoot = ob_get_clean();

// echo $HTML->index($posts, $pagination, $link, $columns_heads, $tfoot, $status);