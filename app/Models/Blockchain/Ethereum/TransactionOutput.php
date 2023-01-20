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
 * @property string value_type
 * @property string type
 */
class TransactionOutput extends Model
{
    use HasFactory;

    protected $table = 'ethereum_transaction_outputs';
    public $timestamps = false;
}
