<?php

namespace App\Mobile\Controllers;

use Illuminate\Http\Request;
use App\Mobile\Resources\ProfileResource;
use App\User;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Controllers\Controller;

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
     * Get the current logged in user's profile.
     *
     * @return \Illuminate\Http\Response
     */
    public function user()
    {
        return new ProfileResource(auth()->user());
    }
}