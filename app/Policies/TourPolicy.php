<?php

namespace App\Policies;

use App\User;
use App\Tour;
use Illuminate\Auth\Access\HandlesAuthorization;

class TourPolicy
{
    use HandlesAuthorization;

    public function before($user, $ability)
    {
        // if logged into the admin side and is an admin
        if (starts_with(\Route::getCurrentRoute()->getPrefix(), 'admin') &&
            ($user->role == 'superadmin' || $user->role == 'admin')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the tour.
     *
     * @param  \App\User  $user
     * @param  \App\Tour  $tour
     * @return mixed
     */
    public function view(User $user, Tour $tour)
    {
        return $user->id == $tour->user_id;
    }

    /**
     * Determine whether the user can create tours.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->role == 'client';
    }

    /**
     * Determine whether the user can update the tour.
     *
     * @param  \App\User  $user
     * @param  \App\Tour  $tour
     * @return mixed
     */
    public function update(User $user, Tour $tour)
    {
        return $user->id == $tour->user_id;
    }

    /**
     * Determine whether the user can delete the tour.
     *
     * @param  \App\User  $user
     * @param  \App\Tour  $tour
     * @return mixed
     */
    public function delete(User $user, Tour $tour)
    {
        return $user->id == $tour->user_id;
    }
}
