<?php

namespace App\Models\Visor;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Address
 *
 * @property string $type
 * @property string address
 * @property string $status
 * @property Carbon last_transaction_at
 *
 */
class Address extends Model
{
    use HasFactory;

    protected $table = 'visor_addresses';
    protected $fillable = ['address', 'type'];
}
