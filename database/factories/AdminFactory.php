<?php

use Faker\Generator as Faker;

$factory->define(App\Admin::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->companyEmail,
        'password' => bcrypt('qweqwe'),
        'remember_token' => str_random(10),
        'tour_limit' => 999,
    ];
});
