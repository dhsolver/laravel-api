<?php

namespace App\Mobile\Controllers;

use \Illuminate\Http\Request;
use App\Http\Requests\ChangeEmailRequest;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;

class ChangeEmailController extends Controller
{
    /**
     * Log the user's request to change email and send activation code.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function request(Request $request)
    {
        $request->validate([
            'email' => [
                'required',
                'confirmed',
                'email',
                'max:255',
                Rule::unique('users')->where(function($query) {
                    $query->where('user_type', 2);
                })
            ],
        ], [
            'email.unique' => 'A user with that email already exists.',
            'email.*' => 'A valid email address is required.'
        ]);

        ChangeEmailRequest::create([
            'user_id' => auth()->id(),
            'new_email' => strtolower($request->email),
            'activation_code' => strtoupper(Str::random(6)),
            'expires_at' => Carbon::now()->addMinutes(30)
        ]);

        return $this->success('Activation code sent');
    }

    /**
     * Confirm the change email request.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:7|min:6'
        ],
        [
            'code.*' => 'Invalid activation code.'
        ]);

        $cer = ChangeEmailRequest::where('activation_code', str_replace('-', '', strtoupper($request->code)))->first();
        if (empty($cer)) {
            return $this->fail(404, 'Invalid activation code.');
        }

        if ($cer->user_id != auth()->id()) {
            return $this->fail(403, 'Unauthorized');
        }

        if (! empty($cer->confirmed_at)) {
            return $this->fail(419, 'Activation code has already been used.');
        }

        if ($cer->expires_at < Carbon::now()) {
            return $this->fail(419, 'Activation code has expired.');
        }

        $cer->update(['confirmed_at' => Carbon::now()]);
        $cer->user->update(['email' => $cer->new_email]);

        return $this->success("Email successfully changed to $cer->new_email");
    }
}
