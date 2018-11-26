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

        /**
         * The total decision time added for a whole tour.
         */
        'decision_time' => env('POINTS_DECISION_TIME', 5),

        /**
         * The total number of points that can be awarded for adventure tours.
         */
        'max_points' => env('POINTS_MAX', 200),

        /**
         * The minimum number of points that can be awarded for adventure tours.
         */
        'min_points' => env('POINTS_MIN', 50),

        /**
         * The percent of points that must be reached in order to
         * award a user a trophy.
         */
        'trophy_percent' => env('POINTS_TROPHY_PERCENT', 0.70),

        /**
         * The amount of points awarded for each stop visited for non-adventure tours.
         */
        'per_stop' => env('POINTS_PER_STOP', 1),

        /**
         * The number of points to take from a score for each question a user skips.
         */
        'skip_penalty' => 10,
    ],

    /**
     * Holds the containing path the the backup of the iTourMobile tourfiles folder.
     */
    'itourfiles' => env('ITOUR_BACKUP_DIR'),
];
