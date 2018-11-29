<?php

namespace App\Policies;

use App\User;
use App\MobileUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class MobileUserPolicy
{
    use HandlesAuthorization;

    /**
     * Master check that runs before all other methods.
     *
     * @param \App\User $user
     * @param string $ability
     * @return bool
     */
    public function before($user, $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the mobileUser.
     *
     * @param \App\User $authUser
     * @param \App\MobileUser $user
     * @return mixed
     */
    public function view(User $authUser, MobileUser $user)
    {
        return $authUser->id == $user->id;
    }

    /**
     * Determine whether the user can create mobileUsers.
     *
     * @param \App\User $authUser
     * @return mixed
     */
    public function create(User $authUser)
    {
        return false;
    }

    /**
     * Determine whether the user can update the mobileUser.
     *
     * @param \App\User $authUser
     * @param \App\MobileUser $user
     * @return mixed
     */
    public function update(User $authUser, MobileUser $user)
    {
        return $authUser->id == $user->id;
    }

    /**
     * Determine whether the user can delete the mobileUser.
     *
     * @param \App\User $authUser
     * @param \App\MobileUser $user
     * @return mixed
     */
    public function delete(User $authUser, MobileUser $user)
    {
        return false;
    }
}
