<?php

use Faker\Generator as Faker;

$factory->define(App\Client::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->companyEmail,
        'password' => bcrypt('qweqwe'),
        'remember_token' => str_random(10),
        'company_name' => $faker->company,
        'tour_limit' => 3,
    ];
});
