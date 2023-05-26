<?php

namespace App\Models\ToolResult\Address;

use App\Models\Blockchain\DepthSync;
use App\Models\Workspace\Board\Board;
use App\Models\Workspace\Board\BoardJob;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $job_id
 * @property int $board_id
 * @property int $depth_sync_id
 * @property array $settings
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class DeepNeighbors extends Model
{
    protected $table = 'tool_address_deep_neighbors_results';

    protected $casts = ['settings' => 'array'];

    public function boardJob()
    {
        return $this->belongsTo(BoardJob::class, 'job_id');
    }

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function depthSync()
    {
        return $this->belongsTo(DepthSync::class);
    }
}
