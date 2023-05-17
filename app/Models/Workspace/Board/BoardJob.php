<?php

namespace App\Models\Workspace\Board;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property integer $id
 * @property integer $board_id
 * @property string $jobable_type
 * @property integer $jobable_id
 * @property boolean $finished
 * @property Carbon $finished_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Board $board
 */
class BoardJob extends Model
{
    use HasFactory;

    public $dates = ['finished_at'];

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function jobable(): MorphTo
    {
        return $this->morphTo('jobable');
    }
}
