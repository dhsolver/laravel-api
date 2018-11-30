<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\TourJoined;
use App\Listeners\MarkTourDownloadedListener;
use App\Events\UserWasRegistered;
use App\Listeners\SendWelcomeEmail;
use App\Events\ChangeEmailRequestCreated;
use App\Listeners\SendChangeEmailActivation;

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
        ChangeEmailRequestCreated::class => [
            SendChangeEmailActivation::class,
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
