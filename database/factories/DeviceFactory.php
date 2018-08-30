<?php

use Faker\Generator as Faker;
use App\Os;
use App\DeviceType;
use Illuminate\Support\Arr;

$factory->define(App\Device::class, function (Faker $faker) {
    return [
        'device_udid' => md5(uniqid()),
        'os' => Arr::random(Os::all()),
        'type' => Arr::random(DeviceType::all()),
        'user_agent' => null,
    ];
});
