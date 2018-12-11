<?php

use Faker\Generator as Faker;

$factory->define(App\TourStat::class, function (Faker $faker) {
    return [
        'downloads' => $faker->numberBetween(0, 9999),
        'time_spent' => $faker->numberBetween(0, 9909),
        'actions' => $faker->numberBetween(0, 500),
        'final' => 1,
    ];
});
