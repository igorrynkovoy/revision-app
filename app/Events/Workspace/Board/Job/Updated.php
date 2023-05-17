<?php

namespace App\Events\Workspace\Board;

use App\Http\Resources\Blockchain\DepthSyncResource;
use App\Http\Resources\Workspaces\Boards\BoardResource;
use App\Http\Resources\Workspaces\Boards\Jobs\JobResource;
use App\Models\Blockchain\DepthSync;
use App\Models\Workspace\Board\Board;
use App\Models\Workspace\Board\BoardJob;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Updated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $boardJob;
    protected int $boardId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(BoardJob $boardJob)
    {
        $this->boardId = $boardJob->board_id;
        $this->boardJob = (new JobResource($boardJob))->resolve();
    }

    public function broadcastAs()
    {
        return 'board.job.updated';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('workspace.boards.' . $this->boardId);
    }
}
