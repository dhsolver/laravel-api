<?php

return [
    'password_reset_url' => env('SUPPORT_URL') . '/#/reset-password',

    /**
     * Config for image processing site wide.
     */
    'imaging' => [
        /**
         * The maximum allowed file size of an uploaded image.
         */
        'max_file_size' => env('MAX_IMAGE_KB', 15000), // in KB

        /**
         * The width and height of image thumbnail files.
         * Sizes are assumed squares, so a value of 400 is equal to 400x400.
        */
        'image_thumb_size' => 400,

        /**
         * Maximum height or width of an image.
         */
        'max_image_size' => 3000,

        /**
         * The width and height of icons (custom map pins)
         */
        'icon_size' => 164,

        /**
         * Maximum icon size - applies to map pins and trophies.
         */
        'max_icon_size' => 3000,

        /**
         * Size to fit the avatar uploads.
         */
        'avatar_size' => 750,
    ],

    /**
     * Config for image processing site wide.
     */
    'audio' => [
        /**
         * The maximum allowed file size of an uploaded image.
         */
        'max_file_size' => env('MAX_AUDIO_KB', 30000), // in KB
    ],

    /**
     * Config for the points calculation system.
     */
    'points' => [
        /**
         * The average walking speed (miles per hour) of a human.  Used to
         * calculate the time it should take to get from one place to another.
         */
        'average_walking_speed' => env('POINTS_WALK_SPEED', 4),
    ],

    /**
     * Holds the containing path the the backup of the iTourMobile tourfiles folder.
     */
    'itourfiles' => env('ITOUR_BACKUP_DIR'),
];
