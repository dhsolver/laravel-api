<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Password;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    use ResetsPasswords, SendsPasswordResetEmails {
        ResetsPasswords::broker insteadof SendsPasswordResetEmails;
    }

    /**
     * Dispatch the reset password link to the User.
     *
     * @return \Illuminate\Http\Response
     */
    public function forgot()
    {
        $this->validateEmail(request());

        $response = $this->broker()->sendResetLink(
            request()->only('email')
        );

        if ($response == Password::RESET_LINK_SENT) {
            return $this->success('Password reset link has been sent to ' . request()->email . '.');
        }

        return $this->fail(401, 'Unable to send the password reset link.  Please double check you have entered the correct email.');
    }

    /**
     * Get the response for a successful password reset.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $response
     * @return \Illuminate\Http\Response
     */
    protected function sendResetResponse(Request $request, $response)
    {
        return $this->success('Your password has been reset.');
    }

    /**
     * Get the response for a failed password reset.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $response
     * @return \Illuminate\Http\Response
     */
    protected function sendResetFailedResponse(Request $request, $response)
    {
        if ($response === Password::INVALID_TOKEN) {
            return $this->fail(400, 'The password reset token has expired.  Please request a new one.');
        } elseif ($response === Password::INVALID_USER) {
            return $this->fail(400, 'Invalid user.');
        } elseif ($response === Password::INVALID_PASSWORD) {
            return $this->fail(400, 'Invalid password.');
        }

        return $this->fail(500, 'An unexpected error occurred.  Please try again.');
    }
}
