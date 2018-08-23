<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use App\Device;

class TourJoined
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $tour;
    public $user;
    public $device_id;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($tour, $user, $device_id)
    {
        $this->tour = $tour;
        $this->user = $user;
        $this->device_id = $device_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
