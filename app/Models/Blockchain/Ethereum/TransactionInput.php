<?php

namespace App\Models\Blockchain\Ethereum;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer id
 * @property string address
 * @property string address_type
 * @property string transaction_hash
 * @property integer index
 * @property float value
 */
class TransactionInput extends Model
{
    use HasFactory;

    protected $table = 'ethereum_transaction_inputs';
    public $timestamps = false;
}
