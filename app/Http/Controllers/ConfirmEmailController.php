<?php

namespace App\Http\Controllers;

use App\User;

class ConfirmEmailController extends Controller
{
    /**
     * Validate email confirmation token.
     *
     * @param string $token
     * @return void
     */
    public function confirm()
    {
        if ($user = User::confirmEmail(request()->token)) {
            return $this->success('Your email has been confirmed.');
        }

        return $this->fail(404);
    }
}
