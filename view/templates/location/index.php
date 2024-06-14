<?php
use App\HTML\LocationHTML;

$HTML = new LocationHTML($router, $table);
echo $HTML->head($title);
echo $HTML->collection_index($posts);
echo $HTML->map();