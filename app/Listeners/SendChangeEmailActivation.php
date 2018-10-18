<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\ChangeEmailRequestCreated;
use App\Mail\ChangeEmailActivation;
use Illuminate\Support\Facades\Mail;

class SendChangeEmailActivation implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param \App\Events\ChangeEmailRequestCreated  $event
     * @return void
     */
    public function handle(ChangeEmailRequestCreated $event)
    {
        Mail::to($event->request->new_email)
            ->send(new ChangeEmailActivation($event->request));
    }
}
