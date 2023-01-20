<?php

namespace App\Models\Project;

use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property string address
 * @property string blockchain
 * @property string tag
 * @property string label
 * @property string description
 * @property int project_id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 */
class AddressLabel extends Model
{
    protected $table = 'project_address_labels';

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
