<?php

namespace App;

class Os
{
    /*
    |--------------------------------------------------------------------------
    | Device Operating System Constants
    |--------------------------------------------------------------------------
    |
    | These constants are helpers for 'os' field for devices in the
    | analytics system.
    |
    */

    const ANDROID = 'android';
    const IOS = 'ios';
    const WINDOWS = 'windows';
    const MAC = 'mac';
    const LINUX = 'linux';
    const OTHER = 'other';

    /**
     * Get a list of all of the device operating systems.
     *
     * @return array
     */
    public static function all()
    {
        return [
            self::ANDROID,
            self::IOS,
            self::WINDOWS,
            self::MAC,
            self::LINUX,
            self::OTHER
        ];
    }
}
