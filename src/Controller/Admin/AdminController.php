<?php

namespace App\Controller\Admin;

use App\Auth\IsGranted;
use App\Controller\AppController;

class AdminController extends AppController
{
    protected string $layout = 'admin';
}