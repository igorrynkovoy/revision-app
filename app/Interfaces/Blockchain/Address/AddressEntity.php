<?php

namespace App\Interfaces\Blockchain\Address;

/**
 * @property string address
 * @property string blockchain_transactions
 */
interface AddressEntity
{
    public function isSynced(): bool;
}
