<?php

namespace App\Audio;

use App\Exceptions\InvalidAudioFileException;
use FFMpeg\FFProbe;

class AudioProcessor
{
    /**
     * The ffprobe instance.
     *
     * @var FFMpeg\FFProbe
     */
    public $ffprobe;

    /**
     * Create a new AudioProcessor instance.
     *
     * @param array $ffmpegConfig
     */
    public function __construct($ffmpegConfig)
    {
        $this->ffprobe = FFProbe::create($ffmpegConfig);
    }

    /**
     * Get the duration of an audio file.
     * Result is in seconds, rounded to the nearest whole number.
     *
     * @param string $filename
     * @throws InvalidAudioFileException
     * @return int
     */
    public function getDuration($filename)
    {
        try {
            $length = $this->ffprobe
                ->format($filename)
                ->get('duration');

            if (is_numeric($length) && $length > 0) {
                return (int) round($length);
            }

            throw new InvalidAudioFileException('Invalid audio file.');
        } catch (\Exception $ex) {
            throw new InvalidAudioFileException('Invalid audio file.');
        }
    }
}
