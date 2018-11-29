<?php

namespace App\Policies;

use App\User;
use App\Admin;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdminPolicy
{
    use HandlesAuthorization;

    /**
     * Master check that runs before all other methods.
     *
     * @param \App\User $user
     * @param string $ability
     * @return bool
     */
    public function before(User $user, $ability)
    {
        if ($user->role == 'superadmin') {
            return true;
        }
    }

    /**
     * Determine whether the user can view the admin.
     *
     * @param \App\User $user
     * @param \App\Admin $admin
     * @return bool
     */
    public function view(User $user, Admin $admin)
    {
        return $user->role == 'admin';
    }

    /**
     * Determine whether the user can create admins.
     *
     * @param \App\User $user
     * @return bool
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the admin.
     *
     * @param \App\User $user
     * @param \App\Admin $admin
     * @return bool
     */
    public function update(User $user, Admin $admin)
    {
        // an admin can update itself
        return $user->role == 'admin' && $user->id === $admin->id;
    }

    /**
     * Determine whether the user can delete the admin.
     *
     * @param \App\User $user
     * @param \App\Admin $admin
     * @return bool
     */
    public function delete(User $user, Admin $admin)
    {
        return false;
    }
}
