<?php
use App\HTML\LocationHTML;

$HTML = new LocationHTML($router, $table);
echo $HTML->head($title, $edit_link);
echo $HTML->collection_index($posts);
echo $HTML->map();