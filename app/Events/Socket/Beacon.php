<?php

namespace App\Events\Socket;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Beacon implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $rand;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->rand = mt_rand(100000, 999999);
    }

    public function broadcastAs()
    {
        return 'socket.beacon';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('general');
    }
}
