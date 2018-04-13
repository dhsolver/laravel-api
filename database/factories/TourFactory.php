<?php

use Faker\Generator as Faker;
use App\Tour;

$factory->define(App\Tour::class, function (Faker $faker) {
    $user = factory('App\User')->create();
    $user->assignRole('client');
    return [
        'user_id' => $user->id,
        'title' => $faker->sentence(),
        'description' => $faker->paragraph(),
        'pricing_type' => Tour::$PRICING_TYPES[array_rand(Tour::$PRICING_TYPES)],
        'type' => Tour::$TOUR_TYPES[array_rand(Tour::$TOUR_TYPES)],
    ];
});
