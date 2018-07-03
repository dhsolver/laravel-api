<?php

return [
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
        'image_thumb_size' => 250,

        /**
         * Maximum height or width of an image.
         */
        'max_image_size' => 3000,

        /**
         * The width and height of icons (custom map pins)
         */
        'icon_size' => 48,

        /**
         * Maximum icon size - applies to map pins and trophies.
         */
        'max_icon_size' => 3000,
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
     * Holds the containing path the the backup of the iTourMobile tourfiles folder.
     */
    'itourfiles' => env('ITOUR_BACKUP_DIR'),
];
