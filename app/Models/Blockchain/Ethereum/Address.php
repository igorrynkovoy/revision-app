<?php

namespace App\Models\Blockchain\Ethereum;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer hidden
 * @property string address
 * @property integer synced_block_number
 * @property string last_transaction_hash
 * @property integer synced_transactions
 * @property integer blockchain_transactions
 * @property integer blockchain_last_tx_block
 * @property Carbon last_sync_at
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Carbon blockchain_data_updated_at
 */
class Address extends Model
{
    use HasFactory;

    const BLOCKCHAIN_SYMBOL = 'ETH';
    const BLOCKCHAIN_NAME = 'Ethereum';

    protected $table = 'ethereum_addresses';

    protected $fillable = ['address'];

    protected $dates = ['last_sync_at', 'blockchain_data_updated_at'];

    public function transactions()
    {
        return $this->belongsToMany(Transaction::class, 'ethereum_transactions_addresses', 'address', 'transaction_hash', 'address', 'hash');
    }
}
