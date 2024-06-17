<?php
use App\HTML\CategoryHTML;

$HTML = new CategoryHTML($router, $table);
echo $HTML->head($title);
echo $HTML->collection_index($posts);