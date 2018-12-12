<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\MobileUser;
use App\Http\Requests\Admin\CreateMobileUserRequest;
use App\Http\Resources\MobileUserResource;
use App\Http\Requests\Admin\UpdateMobileUserRequest;

class MobileUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function index()
    {
        return MobileUserResource::collection(MobileUser::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateMobileUserRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateMobileUserRequest $request)
    {
        if ($user = MobileUser::create($request->validated())) {
            return $this->success("{$user->name} was added successfully.", new MobileUserResource($user));
        }

        return $this->fail();
    }

    /**
     * Display the specified resource.
     *
     * @param \App\MobileUser $user
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function show(MobileUser $user)
    {
        return new MobileUserResource($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateMobileUserRequest $request
     * @param \App\MobileUser $user
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateMobileUserRequest $request, MobileUser $user)
    {
        if ($user->update($request->validated())) {
            $user = $user->fresh();
            return $this->success("{$user->name} was updated successfully.", new MobileUserResource($user));
        }

        return $this->fail();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\MobileUser $user
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(MobileUser $user)
    {
        if ($user->delete()) {
            return $this->success("{$user->name} was archived successfully.");
        }

        return $this->fail();
    }
}
