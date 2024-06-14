<?php
use App\HTML\PhotoHTML;

$HTML = new PhotoHTML($router, $table);
echo $HTML->head($title);
echo $HTML->filter($filter_locations, $filter_categories);
echo $HTML->photo_index($photos);
echo $HTML->lightbox($photos);
echo $HTML->pagination($pagination, $link);