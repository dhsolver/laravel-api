<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use App\Http\Requests\ChangeEmailRequest;

class ChangeEmailRequestCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Get the change email request object.
     *
     * @var ChangeEmailRequest
     */
    public $request;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ChangeEmailRequest $request)
    {
        $this->request = $request;
    }
}
