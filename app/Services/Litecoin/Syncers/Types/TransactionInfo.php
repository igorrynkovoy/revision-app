<?php

namespace App\Services\Litecoin\Syncers\Types;

class TransactionInfo
{
    private int $blockHeight;
    private string $block_hash;
    private string $hash;
    private int $vin_sz;
    private int $vout_sz;
    private int $fees;
    private int $total;
    private int $confirmed;
    private array $inputs;
    private array $outputsx;

}
