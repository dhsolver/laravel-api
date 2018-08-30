<?php

namespace App;

class DeviceType
{
    const PHONE = 'phone';
    const TABLET = 'tablet';
    const WEB = 'web';
    const MOBILE_WEB = 'mobile_web';
    const UNKNOWN = 'unknown';

    public static function all()
    {
        return [
            self::PHONE,
            self::TABLET,
            self::WEB,
            self::MOBILE_WEB,
            self::UNKNOWN,
        ];
    }
}
