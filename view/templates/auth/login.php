<?php
use App\HTML\UserHTML;

$user = new UserHTML($router);
echo $user->head($title);
echo $user->login();