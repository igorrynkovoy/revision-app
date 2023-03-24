<?php

namespace App\Interfaces\Blockchain\Address;

use Carbon\Carbon;

/**
 * @property string address
 * @property integer blockchain_transactions
 * @property Carbon blockchain_data_updated_at
 */
interface AddressEntity
{
    public function isSynced(): bool;

    public function isSynced2(): bool;
}
