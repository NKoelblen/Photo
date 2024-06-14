<?php
use App\HTML\AppHTML;

$HTML = new AppHTML($router);
echo $HTML->head($title);