<?php

namespace App\Events\DepthSync;

use App\Http\Resources\Blockchain\DepthSyncResource;
use App\Models\Blockchain\DepthSync;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Updated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $depthSync;
    private $rootSyncId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(DepthSync $depthSync)
    {
        $this->depthSync = (new DepthSyncResource($depthSync))->resolve();
        $this->rootSyncId = $depthSync->root_sync_id;
    }

    public function broadcastAs()
    {
        return 'depth.sync.updated';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel(empty($this->rootSyncId) ? 'depth.sync.list' : 'depth.sync.' . $this->rootSyncId);
    }
}
