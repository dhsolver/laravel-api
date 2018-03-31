<?php

use Faker\Generator as Faker;
use App\TourStop;

$factory->define(App\TourStop::class, function (Faker $faker) {
    return [
        'title' => $faker->title(),
        'description' => $faker->sentence(),
        'location_type' => TourStop::$LOCATION_TYPES[array_rand(TourStop::$LOCATION_TYPES)],
    ];
});
