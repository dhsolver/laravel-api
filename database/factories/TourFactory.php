<?php

use Faker\Generator as Faker;
use App\Tour;
use Carbon\Carbon;

$factory->define(App\Tour::class, function (Faker $faker) {
    $client = factory('App\Client')->create();

    return [
        'user_id' => $client->id,
        'title' => $faker->sentence(),
        'description' => $faker->paragraph(),
        'pricing_type' => Tour::$PRICING_TYPES[array_rand(Tour::$PRICING_TYPES)],
        'type' => \App\TourType::all()[array_rand(\App\TourType::all())],
        'in_app_id' => 'com.wejunket.' . $faker->word(),
    ];
});

$factory->state(App\Tour::class, 'published', function (Faker $faker) {
    $client = factory('App\Client')->create();

    return [
        'user_id' => $client->id,
        'title' => $faker->sentence(),
        'description' => $faker->paragraph(),
        'pricing_type' => Tour::$PRICING_TYPES[array_rand(Tour::$PRICING_TYPES)],
        'type' => \App\TourType::all()[array_rand(\App\TourType::all())],
        'published_at' => Carbon::now(),
        'in_app_id' => 'com.wejunket.' . $faker->word(),
    ];
});
