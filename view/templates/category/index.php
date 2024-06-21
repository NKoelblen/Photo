<?php
use App\HTML\CategoryHTML;

$HTML = new CategoryHTML($router, $table);
echo $HTML->head($title, $edit_link);
echo $HTML->collection_index($posts);