<?php

use Faker\Generator as Faker;

$factory->define(App\MobileUser::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->companyEmail,
        'password' => bcrypt('qweqwe'),
        'remember_token' => str_random(10),
        'zipcode' => $faker->postcode,
        'tour_limit' => 3,
    ];
});
