<?php

namespace App\Http\Controllers\Admin;

use App\Admin;
use App\Client;
use App\Http\Controllers\Controller;
use App\MobileUser;
use Illuminate\Http\Request;
use App\User;

class ChangeRoleController extends Controller
{
    /**
     * Change a user's role.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\User $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        if ($user->hasRole($request->role)) {
            return $this->success('User is already assigned to this role.');
        }

        if ($user->id == auth()->id()) {
            return $this->fail(500, 'You cannot change your own role.');
        }

        $this->deleteRoleObject($user->role, $user->id);
        $user->removeRole($user->role);
        $user->assignRole($request->role);
        $this->createRoleObject($request->role, $user->id);
        return $this->success('User role has been changed successfully.');
    }

    /**
     * Create a related role class object for the given ID.
     *
     * @param string $role
     * @param int $id
     * @return bool
     */
    public function createRoleObject($role, $id)
    {
        switch ($role) {
            case 'user':
                MobileUser::forceCreate(['id' => $id]);
                break;
            case 'admin':
                Admin::forceCreate(['id' => $id]);
                break;
            case 'client':
                Client::forceCreate(['id' => $id]);
                break;
            default:
                return false;
        }

        return true;
    }

    /**
     * Delete the related role class object for the given ID.
     *
     * @param string $role
     * @param int $id
     * @return bool
     */
    public function deleteRoleObject($role, $id)
    {
        switch ($role) {
            case 'user':
            case 'mobileuser':
                MobileUser::where('id', $id)->forceDelete();
                break;
            case 'admin':
                Admin::where('id', $id)->forceDelete();
                break;
            case 'client':
                Client::where('id', $id)->forceDelete();
                break;
            default:
                return false;
        }

        return true;
    }
}
