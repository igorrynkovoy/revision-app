<?php

namespace App\Models\Workspace\Board;

use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property integer $id
 * @property string $title
 * @property boolean $starred
 * @property integer $workspace_id
 * @property Workspace $workspace
 * @property Collection $layouts
 * @property Collection $jobs
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 */
class Board extends \Eloquent
{
    protected $casts = ['starred' => 'boolean'];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function layouts()
    {
        return $this->hasMany(BoardLayout::class);
    }

    public function jobs()
    {
        return $this->hasMany(BoardJob::class);
    }
}
