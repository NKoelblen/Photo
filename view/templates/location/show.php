<?php
use App\HTML\LocationHTML;
use App\HTML\PhotoHTML;

$HTML = new LocationHTML($router, $table);
echo $HTML->head($title, $edit_link);
echo $HTML->recursive_breadcrumb($post);
if ($post->get_children()):
    echo $HTML->collection_index($post->get_children());
endif;
echo $HTML->map();

$photo_HTML = new PhotoHTML($router, 'photo');
echo $photo_HTML->filter([], $filter_categories);
echo $photo_HTML->photo_index($photos);
echo $photo_HTML->lightbox($photos);
echo $photo_HTML->pagination($pagination, $link);