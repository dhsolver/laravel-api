<?php

use Faker\Generator as Faker;

$factory->define(App\Media::class, function (Faker $faker) {
    return [
        'file' => $faker->imageUrl($width = 640, $height = 480),
    ];
});
