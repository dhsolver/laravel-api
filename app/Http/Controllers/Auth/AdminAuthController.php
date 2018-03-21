<?php

namespace App\Http\Controllers\Auth;

class AdminAuthController extends AuthController
{
    protected $role = 'admin';

    protected $allowsRegistration = false;
}
