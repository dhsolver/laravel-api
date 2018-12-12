<?php

namespace App\Policies;

use App\User;
use App\Media;
use Illuminate\Auth\Access\HandlesAuthorization;

class MediaPolicy
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
        // if user is an admin they can access all media
        if ($user->isAdmin()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the media.
     *
     * @param \App\User $user
     * @param \App\Media $media
     * @return mixed
     */
    public function view(User $user, Media $media)
    {
        return true;
    }

    /**
     * Determine whether the user can create media.
     *
     * @param \App\User $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasRole(['client', 'admin', 'superadmin']);
    }

    /**
     * Determine whether the user can update the media.
     *
     * @param \App\User $user
     * @param \App\Media $media
     * @return mixed
     */
    public function update(User $user, Media $media)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the media.
     *
     * @param \App\User $user
     * @param \App\Media $media
     * @return mixed
     */
    public function delete(User $user, Media $media)
    {
        return false;
    }
}
