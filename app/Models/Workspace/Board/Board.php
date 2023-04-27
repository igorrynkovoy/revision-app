<?php

namespace App\Models\Workspace\Board;

use App\Models\Workspace;
use Carbon\Carbon;

/**
 * @property integer $id
 * @property string $title
 * @property boolean $starred
 * @property integer $workspace_id
 * @property Workspace $workspace
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
}
