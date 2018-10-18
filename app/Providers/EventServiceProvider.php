<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\TourJoined;
use App\Listeners\MarkTourDownloadedListener;
use App\Events\UserWasRegistered;
use App\Listeners\SendWelcomeEmail;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\Event' => [
            'App\Listeners\EventListener',
        ],
        TourJoined::class => [
            MarkTourDownloadedListener::class,
        ],
        UserWasRegistered::class => [
            SendWelcomeEmail::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
