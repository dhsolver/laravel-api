<?php

namespace App\Http\Controllers\Auth;

class MobileAuthController extends AuthController
{
    protected $role = "user";

    protected $allowsRegistration = true;
}
