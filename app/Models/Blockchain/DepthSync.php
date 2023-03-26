<?php

namespace App\Models\Blockchain;

use App\Interfaces\Blockchain\Address\AddressEntity;
use App\Models\Blockchain\Litecoin;
use App\Models\Blockchain\Ethereum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property integer id
 * @property string blockchain
 * @property string address
 * @property integer child_addresses
 * @property integer root_sync_id
 * @property integer parent_sync_id
 * @property string direction
 * @property integer limit_addresses
 * @property integer limit_transactions
 * @property integer max_depth
 * @property integer current_depth
 * @property bool address_synced
 * @property bool processed
 * @property Carbon processed_at
 * @property string processed_code
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property AddressEntity addressModel
 */
class DepthSync extends Model
{
    const DIRECTION_BOTH = 'both';
    const DIRECTION_RECIPIENT = 'recipient';
    const DIRECTION_SENDER = 'sender';

    protected $casts = ['processed' => 'bool', 'address_synced' => 'bool'];

    public function isRoot(): bool
    {
        return is_null($this->root_sync_id);
    }

    public function addressModel(): BelongsTo
    {
        $relation = match ($this->blockchain) {
            Litecoin\Address::BLOCKCHAIN_SYMBOL => $this->belongsTo(Litecoin\Address::class, 'address', 'address'),
            Ethereum\Address::BLOCKCHAIN_SYMBOL => $this->belongsTo(Ethereum\Address::class, 'address', 'address'),
            default => function () {
                throw new \RuntimeException('Invalid blockchain');
            }
        };

        return $relation;
    }

    public function children(): HasMany
    {
        return $this->hasMany(DepthSync::class, 'root_sync_id');
    }

    public static function getDirectionsList(): array
    {
        return [self::DIRECTION_BOTH, self::DIRECTION_SENDER, self::DIRECTION_RECIPIENT];
    }

}
