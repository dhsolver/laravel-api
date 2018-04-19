<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\MobileUserCollection;
use App\Http\Controllers\Controller;
use App\MobileUser;
use App\Http\Requests\Admin\CreateMobileUserRequest;
use App\Http\Resources\MobileUserResource;
use App\Http\Requests\Admin\UpdateMobileUserRequest;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\ErrorResponse;

class MobileUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new MobileUserCollection(MobileUser::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateMobileUserRequest $request)
    {
        if ($user = MobileUser::create($request->validated())) {
            return new SuccessResponse("{$user->name} was added successfully.", new MobileUserResource($user));
        }

        return new ErrorResponse(500, 'The User could not be created.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(MobileUser $user)
    {
        return new MobileUserResource($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateMobileUserRequest $request, MobileUser $user)
    {
        if ($user->update($request->validated())) {
            $user = $user->fresh();
            return new SuccessResponse("{$user->name} was updated successfully.", new MobileUserResource($user));
        }

        return new ErrorResponse(500, "{$user->name} could not be saved.");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(MobileUser $user)
    {
        if ($user->delete()) {
            return new SuccessResponse("{$user->name} was archived successfully.");
        }

        return new ErrorResponse(500, "{$user->name} could not be deleted.");
    }
}
