<?php

use Faker\Generator as Faker;

$factory->define(App\DeviceStat::class, function (Faker $faker) {
    return [
        'device_type' => $faker->randomElement(['phone', 'tablet']),
        'os' => $faker->randomElement(['os', 'android']),
        'downloads' => $faker->numberBetween(0, 999),
        'actions' => $faker->numberBetween(0, 999),
        'visitors' => $faker->numberBetween(0, 999),
        'final' => 1,
    ];
});
