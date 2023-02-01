<?php

namespace App\Models\Workspace;

use App\Models\Workspace;
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
 * @property int workspace_id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 */
class AddressLabel extends Model
{
    protected $table = 'workspace_address_labels';

    public $timestamps = false;

    protected $fillable = ['address', 'label', 'description', 'blockchain', 'tag'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }

    public static function getValidationRules()
    {
        return [
            'address' => 'required|string|min:8|max:256',
            'label' => 'string|max:256',
            'description' => 'string|max:512',
            'blockchain' => 'required|string|in:LTC,ETH',
            'tag' => 'string|max:64'
        ];
    }

    public static function getUpdateValidationRules()
    {
        return [
            'address' => 'string|min:8|max:256',
            'label' => 'string|max:256',
            'description' => 'string|max:512',
            'blockchain' => 'string|in:LTC,ETH',
            'tag' => 'string|max:64'
        ];
    }
}
