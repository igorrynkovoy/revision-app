<?php

namespace App\Events\Workspace\Board;

use App\Http\Resources\Blockchain\DepthSyncResource;
use App\Http\Resources\Workspaces\Boards\BoardResource;
use App\Models\Blockchain\DepthSync;
use App\Models\Workspace\Board\Board;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Created implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $board;
    protected int $workspaceId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Board $board)
    {
        $this->workspaceId = $board->workspace_id;
        $this->board = (new BoardResource($board))->resolve();;
    }

    public function broadcastAs()
    {
        return 'workspace.board.created';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('workspace.' . $this->workspaceId . '.boards.list');
    }
}
