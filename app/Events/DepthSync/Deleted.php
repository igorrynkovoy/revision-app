<?php

namespace App\Events\DepthSync;

use App\Http\Resources\Blockchain\DepthSyncResource;
use App\Models\Blockchain\DepthSync;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Deleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $depthSyncId;
    private $rootSyncId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($depthSyncId, $rootSyncId = null)
    {
        $this->depthSyncId = $depthSyncId;
        $this->rootSyncId = $rootSyncId;
    }

    public function broadcastAs()
    {
        return 'depth.sync.deleted';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel(empty($this->rootSyncId) ? 'depth.sync.general' : 'depth.sync.' . $this->rootSyncId);
    }
}
