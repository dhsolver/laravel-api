<?php

namespace App;

class TourType
{
    /*
    |--------------------------------------------------------------------------
    | Tour Type Constants
    |--------------------------------------------------------------------------
    |
    | These constants are helpers for the the 'type' field in
    | the Tours table.
    |
    */

    public const ADVENTURE = 'adventure';
    public const OUTDOOR = 'outdoor';
    public const INDOOR = 'indoor';

    /**
     * Helper method to check if given type is 'adventure'.
     *
     * @param string $type
     * @return bool
     */
    public function isAdventure($type)
    {
        return $type == self::ADVENTURE;
    }

    /**
     * Get all Tour types.
     *
     * @return array
     */
    public static function all()
    {
        return [
            self::ADVENTURE,
            self::OUTDOOR,
            self::INDOOR
        ];
    }
}
