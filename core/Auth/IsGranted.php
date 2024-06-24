<?php

namespace App\Auth;

use App\Security\ForbidenException;
use Attribute;

#[Attribute]
class IsGranted
{
    public function __construct(private string $role = '')
    {
        if (session_status() === PHP_SESSION_NONE):
            session_start();
        endif;
        if ($role === '' && !isset($_SESSION['auth'])):
            throw new ForbidenException();
        else:
            if (!isset($_SESSION['role']) || !$_SESSION['role'] === $role):
                throw new ForbidenException();
            endif;
        endif;
    }
}