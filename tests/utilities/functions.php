<?php

function create($class, $attributes = [], $times = null)
{
    return factory($class, $times)->create($attributes);
}

function make($class, $attributes = [], $times = null)
{
    return factory($class, $times)->make($attributes);
}

function createState($class, $state, $attributes = [])
{
    return factory($class)->states($state)->create($attributes);
}

function createUser($role = 'user', $password = 'secret')
{
    switch ($role) {
        case 'user':
            $cls = 'App\MobileUser';
            break;
        case 'admin':
            $cls = 'App\Admin';
            break;
        case 'superadmin':
            $cls = 'App\SuperAdmin';
            break;
        case 'client':
            $cls = 'App\Client';
            break;
    }

    $user = factory($cls)->create([
        'password' => bcrypt($password)
    ]);

    return $user;
}
