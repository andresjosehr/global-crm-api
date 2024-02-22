<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNotificationEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $user_id;
    private $notification;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user_id, $notification)
    {
        //
        $this->user_id = $user_id;
        $this->notification = $notification;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        Log::info('notification-channel-' . env('NOTIFICATION_KEY') . '-' . $this->user_id);
        return ['notification-channel-' . env('NOTIFICATION_KEY') . '-' . $this->user_id];
    }

    public function broadcastAs()
    {
        return 'notification-event';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'notification' => $this->notification
        ];
    }
}
