<?php

namespace App\Models;

use App\Models\Workspace\Board\Board;
use App\Models\Workspace\Label;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $title
 * @property Collection $labels
 * @property Collection $boards
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 */
class Workspace extends Model
{
    public function labels(): HasMany
    {
        return $this->hasMany(Label::class, 'workspace_id');
    }

    public function boards(): HasMany
    {
        return $this->hasMany(Board::class, 'workspace_id');
    }

}
