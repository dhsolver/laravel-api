<?php

use Faker\Generator as Faker;

$factory->define(App\StopStat::class, function (Faker $faker) {
    return [
        'visits' => $faker->numberBetween(0, 999),
        'time_spent' => $faker->numberBetween(0, 999),
        'actions' => $faker->numberBetween(0, 999),
        'final' => 1,
    ];
});
