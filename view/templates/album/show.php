<?php
use App\HTML\AlbumHTML;
use App\HTML\PhotoHTML;

$HTML = new AlbumHTML($router, $table);
echo $HTML->head($title, $edit_link);

$photo_HTML = new PhotoHTML($router, 'photo');
echo $photo_HTML->filter($filter_locations, $filter_categories);
echo $photo_HTML->photo_index($photos);
echo $photo_HTML->lightbox($photos);
echo $photo_HTML->pagination($pagination, $link);