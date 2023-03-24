<?php

namespace App\Models\Blockchain\Litecoin;

use App\Interfaces\Blockchain\Address\AddressEntity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer id
 * @property string address
 * @property string sync_status
 * @property string sync_status_code
 * @property integer synced_block_number
 * @property integer synced_first_block_number
 * @property integer synced_last_block_number
 * @property string last_transaction_hash
 * @property integer synced_transactions
 * @property integer blockchain_transactions
 * @property integer blockchain_last_tx_block
 * @property integer blockchain_first_tx_block
 * @property Carbon last_sync_at
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Carbon blockchain_data_updated_at
 */
class Address extends Model implements AddressEntity
{
    use HasFactory;

    public const BLOCKCHAIN_SYMBOL = 'LTC';
    public const BLOCKCHAIN_NAME = 'Litecoin';
    public const SYNC_STATUS_SYNCING = 'syncing';
    public const SYNC_STATUS_SUCCESS = 'success';
    public const SYNC_STATUS_FAILED = 'failed';

    protected $table = 'litecoin_addresses';

    protected $fillable = ['address'];

    protected $dates = ['last_sync_at', 'blockchain_data_updated_at'];


    public function transactions()
    {
        return $this->belongsToMany(Transaction::class, 'litecoin_transactions_addresses', 'address', 'transaction_hash', 'address', 'hash');
    }

    public function isSynced(): bool
    {
        return $this->synced_block_number === $this->blockchain_last_tx_block;
    }

    public function isSynced2(): bool
    {
        // TODO: Rename
        // How long data is fresh
        $ttl = 30;

        if($this->blockchain_data_updated_at?->diffInSeconds() > $ttl) {
            return false;
        }

        return $this->synced_block_number === $this->blockchain_last_tx_block;
    }
}
