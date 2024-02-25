<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallActivityEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;
    public $event;
    private $user_id;

    public function __construct($user_id, $event, $data)
    {
        $this->data = $data;
        $this->event = $event;
        $this->user_id = $user_id;
    }

    public function broadcastOn()
    {
        return ['activity-channel-' . env('NOTIFICATION_KEY') . '-' . $this->user_id];
    }

    public function broadcastAs()
    {
        return 'activity-event';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'event' => $this->event,
            'data' => $this->data
        ];
    }
}
