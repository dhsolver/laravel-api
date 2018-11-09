<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Audio\AudioProcessor;

class AudioProcessorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(AudioProcessor::class, function ($app) {
            return new AudioProcessor([
                'ffmpeg.binaries' => config('ffmpeg.binary'),
                'ffprobe.binaries' => config('ffmpeg.ffprobe_binary'),
                'timeout' => config('ffmpeg.timeout'),
                'ffmpeg.threads' => config('ffmpeg.threads'),
            ]);
        });
    }
}
