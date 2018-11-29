<?php

namespace App\Policies;

use App\User;
use App\Client;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClientPolicy
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
     * Determine whether the user can view the client.
     *
     * @param \App\User $user
     * @param \App\Client $client
     * @return bool
     */
    public function view(User $user, Client $client)
    {
        return $user->id == $client->id;
    }

    /**
     * Determine whether the user can create clients.
     *
     * @param \App\User $user
     * @return bool
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the client.
     *
     * @param \App\User $user
     * @param \App\Client $client
     * @return bool
     */
    public function update(User $user, Client $client)
    {
        return $user->id == $client->id;
    }

    /**
     * Determine whether the user can delete the client.
     *
     * @param \App\User $user
     * @param \App\Client $client
     * @return bool
     */
    public function delete(User $user, Client $client)
    {
        return false;
    }
}
