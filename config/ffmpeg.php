<?php

return [
    /**
     * Location of the ffmpeg binary.
     */
    'binary' => env('FFMPEG_BINARY', '/usr/bin/ffmpeg'),

    /**
     * Location of the ffprobe binary.
     */
    'ffprobe_binary' => env('FFPROBE_BINARY', '/usr/bin/ffprobe'),

    /**
     * The underlying timeout for ffmpeg processing.
     */
    'timeout' => env('FFMPEG_TIMEOUT', 3600),

    /**
     * The number of threads ffmpeg should use.
     */
    'threads' => env('FFMPEG_THREADS', 2),
];
