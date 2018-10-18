<?php

namespace App\Http\Controllers;

use App\User;

class ConfirmEmailController extends Controller
{
    public function confirm($token)
    {
        if ($user = User::confirmEmail($token)) {
            return 'Thank you!  Your email has been confirmed.';
        }

        return 'We could not find what you were looking for.';
    }
}
