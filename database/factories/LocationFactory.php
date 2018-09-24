<?php

use Faker\Generator as Faker;

$factory->define(App\Location::class, function (Faker $faker) {
    return [
        'address1' => $faker->streetAddress(),
        'city' => $faker->city,
        'state' => $faker->state,
        'country' => 'US',
        'zipcode' => $faker->postcode,
        'latitude' => $faker->latitude,
        'longitude' => $faker->longitude,
    ];
});
