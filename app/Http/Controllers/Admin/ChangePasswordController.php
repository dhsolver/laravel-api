<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use App\Http\Requests\Admin\ChangePasswordRequest;

class ChangePasswordController extends Controller
{
    /**
     * Change the user's password.
     *
     * @param ChangePasswordRequest $request
     * @param \App\User $user
     * @return \Illuminate\Http\Response
     */
    public function update(ChangePasswordRequest $request, User $user)
    {
        if ($user->update(['password' => bcrypt($request->password)])) {
            return $this->success('Password has been changed.');
        }

        return $this->fail();
    }
}
