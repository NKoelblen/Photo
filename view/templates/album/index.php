<?php
use App\HTML\AlbumHTML;

$HTML = new AlbumHTML($router, $table);
echo $HTML->head($title, $edit_link);
echo $HTML->collection_index($posts);
echo $HTML->pagination($pagination, $link);