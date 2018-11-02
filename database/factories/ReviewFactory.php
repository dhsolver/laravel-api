<?php

use Faker\Generator as Faker;

$factory->define(App\Review::class, function (Faker $faker) {
    $user = factory(\App\User::class)->create();

    if (\App\Tour::count() == 0) {
        $tour = factory(\App\Tour::class)->create();
    } else {
        $tour = \App\Tour::first();
    }

    return [
        'review' => substr($faker->sentence(25), 0, 255),
        'rating' => array_random([0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50]),
        'user_id' => $user->id,
        'tour_id' => $tour->id,
    ];
});
