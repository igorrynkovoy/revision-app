<?php

namespace App\Models;

use App\Models\Workspace\AddressLabel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $title
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 */
class Workspace extends Model
{
    public function addressLabels(): HasMany
    {
        return $this->hasMany(AddressLabel::class, 'workspace_id');
    }

}
