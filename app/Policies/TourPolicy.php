<?php

namespace App\Policies;

use App\User;
use App\Tour;
use Illuminate\Auth\Access\HandlesAuthorization;

class TourPolicy
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
        // if user is an admin they can access any tour
        if ($user->isAdmin()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the tour.
     *
     * @param \App\User $user
     * @param \App\Tour $tour
     * @return mixed
     */
    public function view(User $user, Tour $tour)
    {
        return $user->id == $tour->user_id;
    }

    /**
     * Determine whether the user can create tours.
     *
     * @param \App\User $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasRole(['client', 'admin', 'superadmin']);
    }

    /**
     * Determine whether the user can update the tour.
     *
     * @param \App\User $user
     * @param \App\Tour $tour
     * @return mixed
     */
    public function update(User $user, Tour $tour)
    {
        return $user->id == $tour->user_id;
    }

    /**
     * Determine whether the user can delete the tour.
     *
     * @param \App\User $user
     * @param \App\Tour $tour
     * @return mixed
     */
    public function delete(User $user, Tour $tour)
    {
        return $user->id == $tour->user_id;
    }
}
