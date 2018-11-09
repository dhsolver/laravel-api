<?php

use Faker\Generator as Faker;
use Illuminate\Support\Carbon;

$factory->define(App\ScoreCard::class, function (Faker $faker) {
    $finished_at = new Carbon($faker->dateTimeThisYear()->format('c'));

    return [
        'par' => random_int(60, 120),
//        'points' => random_int(0, 200),
        'total_stops' => random_int(5, 50),
        'stops_visited' => random_int(1, 5),
        'won_trophy_at' => $faker->optional($weight = 0.5, $default = null)->dateTimeThisYear(),
        'started_at' => $finished_at->subMinutes(random_int(60, 120)),
        'finished_at' => $finished_at,
        'is_adventure' => true,
    ];
});
