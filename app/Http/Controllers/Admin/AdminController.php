<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\AdminCollection;
use App\Http\Controllers\Controller;
use App\Admin;
use App\Http\Requests\Admin\CreateAdminRequest;
use App\Http\Resources\AdminResource;
use App\Http\Requests\Admin\UpdateAdminRequest;
use App\Http\Responses\SuccessResponse;
use App\Http\Responses\ErrorResponse;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new AdminCollection(Admin::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateAdminRequest $request)
    {
        if ($admin = Admin::create($request->validated())) {
            return new SuccessResponse("{$admin->name} was added successfully.", new AdminResource($admin));
        }

        return new ErrorResponse(500, 'The User could not be created.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Admin $admin)
    {
        return new AdminResource($admin);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAdminRequest $request, Admin $admin)
    {
        if ($admin->update($request->validated())) {
            $admin = $admin->fresh();
            return new SuccessResponse("{$admin->name} was updated successfully.", new AdminResource($admin));
        }

        return new ErrorResponse(500, "{$admin->name} could not be saved.");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Admin $admin)
    {
        if (auth()->user()->role != 'superadmin') {
            return new ErrorResponse(403, 'Forbidden.');
        }

        if ($admin->delete()) {
            return new SuccessResponse("{$admin->name} was archived successfully.");
        }

        return new ErrorResponse(500, "{$admin->name} could not be deleted.");
    }
}
