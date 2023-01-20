<?php

namespace App\Models;

use App\Models\Project\AddressLabel;
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
class Project extends Model
{
    public function labels(): HasMany
    {
        return $this->hasMany(AddressLabel::class, 'project_id');
    }

}
