<?php

return [
    /**
     * Config for image processing site wide.
     */
    'imaging' => [
        /**
         * The maximum allowed file size of an uploaded image.
         */
        'max_file_size' => env('MAX_IMAGE_KB', 100000), // in KB
    ],
];
