<?php

namespace App\Models\Workspace\Board;

use Carbon\Carbon;

/**
 * @property integer $id
 * @property string $title
 * @property integer $board_id
 * @property array $layout
 * @property Board $board
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 */
class BoardLayout extends \Eloquent
{
    protected $casts = ['layout' => 'json'];

    public function board()
    {
        return $this->belongsTo(Board::class);
    }
}
