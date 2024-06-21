<?php
use App\HTML\Admin\UserHTML;

$HTML = new UserHTML($router, $table, $labels);

echo $HTML->alerts();
echo $HTML->head($title);

echo $HTML->user_index(
    posts: $posts,
    pagination: $pagination,
    link: $link,
);