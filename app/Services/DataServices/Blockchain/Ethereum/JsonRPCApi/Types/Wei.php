<?php

namespace App\Services\DataServices\Blockchain\Ethereum\JsonRPCApi\Types;

class Wei
{
    private $amount;

    public function __construct($amount)
    {
        $this->amount = (string)$amount;
    }

    public function isNotEmpty(): bool
    {
        return bccomp($this->amount, '0') !== 0;
    }

    public function isEmpty(): bool
    {
        return bccomp($this->amount, '0') === 0;
    }

    public function amount(): string
    {
        return $this->amount;
    }

    public function toEther(): string
    {
        return bcdiv($this->amount, "1000000000000000000", 18);
    }

    public function toGwei(): string
    {
        return bcdiv($this->amount, "1000000000", 2);
    }

    public function __toString()
    {
        return $this->amount;
    }

    public static function fromGwei($amount): Wei
    {
        return new Wei(bcmul($amount, 10 ** 9));
    }

    public static function fromEth($amount): Wei
    {
        return new Wei(bcmul($amount, 10 ** 18));
    }
}
