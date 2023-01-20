<?php

namespace App\Models\Blockchain\Ethereum;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $hash
 * @property string $block_hash
 * @property integer $block_number
 * @property integer $gas_usage
 * @property float $gas_price
 * @property float $total_input
 * @property float $total_output
 * @property boolean $processed
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $added_at
 * @property Collection $inputs
 * @property Collection $outputs
 */
class Transaction extends Model
{
    use HasFactory;

    protected $table = 'ethereum_transactions';

    protected $dates = ['added_at'];

    public function inputs()
    {
        return $this->hasMany(TransactionInput::class, 'transaction_hash', 'hash');
    }

    public function outputs()
    {
        return $this->hasMany(TransactionOutput::class, 'transaction_hash', 'hash');
    }
}
