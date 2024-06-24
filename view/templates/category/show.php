<?php
use App\HTML\CategoryHTML;
use App\HTML\PhotoHTML;

$HTML = new CategoryHTML($router, $table);
echo $HTML->head($title, $edit_link);
echo $HTML->recursive_breadcrumb($post);
if ($post->get_children()):
    echo $HTML->collection_index($post->get_children());
endif;

$photo_HTML = new PhotoHTML($router, 'photo');
echo $photo_HTML->filter($filter_locations);
echo $photo_HTML->photo_index($photos);
echo $photo_HTML->lightbox($photos);
echo $photo_HTML->pagination($pagination, $link);