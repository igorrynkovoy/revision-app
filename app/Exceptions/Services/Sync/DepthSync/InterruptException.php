<?php

namespace App\Exceptions\Services\Sync\DepthSync;

class InterruptException extends \Exception
{
    const CODE_MAX_DEPTH = 'max_depth_reached';
    const CODE_ADDRESS_LIMIT = 'foreign_addresses_limit';
    const CODE_TRANSACTIONS_LIMIT = 'transactions_limit';


    private $interruptCode;

    public function __construct(string $message, string $interruptCode)
    {
        $this->interruptCode = $interruptCode;

        parent::__construct($message);
    }

    public function getInterruptCode(): string
    {
        return $this->interruptCode;
    }
}
