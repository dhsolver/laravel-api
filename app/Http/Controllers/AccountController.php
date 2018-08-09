<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\ChangePasswordRequest;
use Auth;

class AccountController extends Controller
{
    /**
     * Get the user's profile information.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        return response()->json(auth()->user()->type);
    }

    /**
     * Update the user's profile information.
     *
     * @param UpdateProfileRequest $request
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProfileRequest $request)
    {
        if (auth()->user()->type->update($request->validated())) {
            return $this->success('Your profile has been updated.', auth()->user()->fresh()->type);
        }

        return $this->error(500, 'An unexpected error occurred while saving your profile information.  Please try again.');
    }

    /**
     * Update the user's profile information.
     *
     * @param UpdateProfileRequest $request
     * @return \Illuminate\Http\Response
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $data = $request->validated();

        $credentials = [
            'email' => auth()->user()->email,
            'password' => $data['old_password'],
        ];

        if (!Auth::attempt($credentials)) {
            return $this->fail(401, 'Your current password was invalid.');
        }

        if (auth()->user()->update([
            'password' => bcrypt($data['password']),
        ])) {
            return $this->success('Your password was changed.');
        }

        return $this->error(500, 'An unexpected error occurred while trying to change your password.  Please try again.');
    }
}
