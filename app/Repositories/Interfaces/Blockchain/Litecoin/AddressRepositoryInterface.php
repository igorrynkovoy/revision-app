<?php

namespace App\Repositories\Interfaces\Blockchain\Litecoin;

use App\Models\Blockchain\Litecoin\Address;

interface AddressRepositoryInterface
{
    public function getAddressByAddress($string): Address;
}
