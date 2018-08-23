<?php

namespace App\Listeners;

use App\Events\TourJoined;
use App\Action;

class MarkTourDownloadedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  TourJoined  $event
     * @return void
     */
    public function handle(TourJoined $event)
    {
        $event->tour->activity()->create([
            'user_id' => $event->user->id,
            'device_id' => $event->device_id,
            'action' => Action::DOWNLOAD,
        ]);
    }
}
