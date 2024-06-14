<?php
use App\HTML\CategoryHTML;
use App\HTML\PhotoHTML;

$HTML = new CategoryHTML($router);
echo $HTML->head($title);
echo $HTML->recursive_breadcrumb($post);
if ($post->get_children()):
    echo $HTML->collection_index($post->get_children());
endif;

$photo_HTML = new PhotoHTML($router);
echo $photo_HTML->filter();
echo $photo_HTML->photo_index();
echo $photo_HTML->lightbox();