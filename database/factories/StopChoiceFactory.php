<?php

use Faker\Generator as Faker;

$factory->define(App\StopChoice::class, function (Faker $faker) {
    return [
        'answer' => $faker->sentence,
        // 'order' => 1,
    ];
});
