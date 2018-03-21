<?php

namespace App\Http\Controllers\Auth;

class CmsAuthController extends AuthController
{
    protected $role = "business";

    protected $allowsRegistration = true;
}
