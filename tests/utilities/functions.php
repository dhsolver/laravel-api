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
    $user = factory('App\User')->create([
        'password' => bcrypt($password)
    ]);

    $user->assignRole($role);

    return $user;
}
