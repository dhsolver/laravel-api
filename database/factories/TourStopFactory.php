<?php

use Faker\Generator as Faker;
use App\TourStop;

$factory->define(App\TourStop::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence(),
        'description' => $faker->paragraph(),
        'order' => 1,
    ];
});
