<?php

namespace App;

class DeviceType
{
    /*
    |--------------------------------------------------------------------------
    | Device Type Constants
    |--------------------------------------------------------------------------
    |
    | These constants are helpers for 'type' field for devices in the
    | analytics system.
    |
    */

    const PHONE = 'phone';
    const TABLET = 'tablet';
    const WEB = 'web';
    const MOBILE_WEB = 'mobile_web';
    const UNKNOWN = 'unknown';

    /**
     * Get a list of all of the device types.
     *
     * @return array
     */
    public static function all()
    {
        return [
            self::PHONE,
            self::TABLET,
            self::WEB,
            self::MOBILE_WEB,
            self::UNKNOWN
        ];
    }
}
