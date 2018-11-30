<?php

namespace App\Events;

use App\Tour;
use App\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class TourJoined
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var \App\Tour
     */
    public $tour;

    /**
     * @var \App\User
     */
    public $user;

    /**
     * @var string
     */
    public $device_id;

    /**
     * Create a new event instance.
     *
     * @param \App\Tour $tour
     * @param \App\User $user
     * @param string $device_id
     * @return void
     */
    public function __construct(Tour $tour, User $user, $device_id)
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
