<?php

namespace App\Models\Blockchain\Litecoin;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $hash
 * @property string $block_hash
 * @property integer $block_number
 * @property integer $total_inputs
 * @property integer $total_outputs
 * @property float $fee
 * @property float $amount
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

    protected $table = 'litecoin_transactions';

    protected $dates = ['added_at'];

    public function inputs()
    {
        return $this->hasMany(TransactionOutput::class, 'input_transaction_hash', 'hash');
    }

    public function outputs()
    {
        return $this->hasMany(TransactionOutput::class, 'transaction_hash', 'hash');
    }
}
