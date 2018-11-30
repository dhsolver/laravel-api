<?php

use Faker\Generator as Faker;
use App\StopChoice;

$factory->define(StopChoice::class, function (Faker $faker) {
    return [
        'answer' => $faker->sentence,
    ];
});
