<?php

namespace App\Services\DataServices\Blockchain\BlockCypher;

class Transaction
{
    public function __construct()
    {

    }

    public static function fromArray(array $data)
    {
        return new self();
    }
}
