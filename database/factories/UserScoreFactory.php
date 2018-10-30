<?php

use Faker\Generator as Faker;
use Illuminate\Support\Carbon;

$factory->define(App\UserScore::class, function (Faker $faker) {
    $finished_at = new Carbon($faker->dateTimeThisYear()->format('c'));

    return [
        'par' => random_int(60, 120),
        'points' => random_int(0, 200),
        'won_trophy' => $faker->optional($weight = 0.5, $default = false)->randomDigit,
        'started_at' => $finished_at->subMinutes(random_int(60, 120)),
        'finished_at' => $finished_at,
    ];
});
