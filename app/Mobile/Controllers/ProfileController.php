<?php

namespace App\Mobile\Controllers;

use Illuminate\Http\Request;
use App\Mobile\Resources\ProfileResource;
use App\User;
use App\Mobile\Requests\UpdateProfileRequest;
use App\Http\Controllers\Controller;
use App\Mobile\Requests\UpdatePasswordRequest;

class ProfileController extends Controller
{
    /**
     * Get the given user's profile.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        return new ProfileResource($user);
    }

    /**
     * Update the current user's profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProfileRequest $request)
    {
        auth()->user()->update($request->validated());

        return new ProfileResource(auth()->user()->fresh());
    }

    /**
     * Change the user's profile.
     *
     * @return void
     */
    public function password(UpdatePasswordRequest $request)
    {
        auth()->user()->update(['password' => bcrypt($request->password)]);

        return $this->success('Your password has been updated.');
    }
}
