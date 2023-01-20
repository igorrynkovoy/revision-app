<?php

namespace App\Models\Blockchain\Litecoin;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property integer id
 * @property string address
 * @property string transaction_hash
 * @property integer index
 * @property integer block_number
 * @property string input_transaction_hash
 * @property integer input_index
 * @property float value
 * @property string script_type
 * @property Carbon created_at
 */
class TransactionOutput extends Model
{
    use HasFactory;

    protected $fillable = ['*'];
    protected $table = 'litecoin_transaction_outputs';
    public $timestamps = false;
    protected $dates = ['created_at'];
}
