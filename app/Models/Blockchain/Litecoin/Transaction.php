<?php

namespace App\Models\Blockchain\Litecoin;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property string $hash
 * @property integer $block_number
 * @property integer $total_inputs
 * @property integer $total_outputs
 * @property float $fee
 * @property float $amount
 * @property boolean $is_coinbase
 * @property boolean $processed
 * @property Carbon $created_at
 * @property Carbon $added_at
 * @property Carbon $processed_at
 * @property Collection $inputs
 * @property Collection $outputs
 */
class Transaction extends Model
{
    use HasFactory;

    protected $table = 'litecoin_transactions';

    public $timestamps = false;

    protected $dates = ['added_at', 'created_at', 'processed_at'];

    protected $casts = ['processed' => 'bool', 'is_coinbase' => 'bool'];

    public function inputs()
    {
        return $this->hasMany(TransactionOutput::class, 'input_transaction_hash', 'hash')->orderBy('input_index');
    }

    public function outputs()
    {
        return $this->hasMany(TransactionOutput::class, 'transaction_hash', 'hash')->orderBy('index');
    }
}
