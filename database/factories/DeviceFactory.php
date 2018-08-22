<?php

use Faker\Generator as Faker;
use App\Os;
use App\DeviceType;

$factory->define(App\Device::class, function (Faker $faker) {
    return [
        'device_udid' => md5(uniqid()),
        'os' => array_rand(Os::all()),
        'type' => array_rand(DeviceType::all()),
        'user_agent' => null,
    ];
});
