<?php

namespace App\Models\Workspace;

use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property string type
 * @property string key
 * @property string blockchain
 * @property string tag
 * @property string label
 * @property string description
 * @property int workspace_id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 */
class Label extends Model
{
    const TYPE_ADDRESS = 'address';
    const TYPE_TRANSACTION = 'transaction';

    protected $table = 'workspace_labels';

    public $timestamps = false;

    protected $fillable = ['type', 'key', 'label', 'description', 'blockchain', 'tag'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }

    public static function getValidationRules()
    {
        return [
            'type' => 'required|string|in:' . implode(',', [self::TYPE_ADDRESS, self::TYPE_TRANSACTION]),
            'key' => 'required|string|min:8|max:256',
            'label' => 'string|max:256',
            'description' => 'nullable|string|max:512',
            'blockchain' => 'required|string|in:LTC,ETH',
            'tag' => 'string|max:64'
        ];
    }

    public static function getUpdateValidationRules()
    {
        return [
            'type' => 'string|in:' . implode(',', [self::TYPE_ADDRESS, self::TYPE_TRANSACTION]),
            'key' => 'string|min:8|max:256',
            'label' => 'string|max:256',
            'description' => 'string|max:512',
            'blockchain' => 'string|in:LTC,ETH',
            'tag' => 'string|max:64'
        ];
    }
}
