<?php

namespace App\Services\DataServices\Blockchain\Ethereum\JsonRPCApi\Methods;

use App\Services\DataServices\Blockchain\Ethereum\JsonRPCApi\Types\Address;
use App\Services\DataServices\Blockchain\Ethereum\JsonRPCApi\Types\Block;
use App\Services\DataServices\Blockchain\Ethereum\JsonRPCApi\Types\BlockHash;
use App\Services\DataServices\Blockchain\Ethereum\JsonRPCApi\Types\BlockNumber;
use App\Services\DataServices\Blockchain\Ethereum\JsonRPCApi\Types\Transaction;
use App\Services\DataServices\Blockchain\Ethereum\JsonRPCApi\Types\TransactionHash;
use App\Services\DataServices\Blockchain\Ethereum\JsonRPCApi\Types\TransactionInfo;
use App\Services\DataServices\Blockchain\Ethereum\JsonRPCApi\Types\TransactionReceipt;
use App\Services\DataServices\Blockchain\Ethereum\JsonRPCApi\Types\Wei;

class Trace extends AbstractMethods
{
    public function replayTransaction(TransactionHash $hash)
    {
        $response = $this->send(
            $this->client->request(67, 'trace_replayTransaction', [$hash->toString(), ['trace']])
        );

        return $response->getRpcResult();
    }
    public function filter()
    {
        $response = $this->send(
            $this->client->request(67, 'trace_filter', [['fromBlock' => '0x'.dechex(8334143), 'toBlock' => '0x'.dechex(8334143)]])
        );

        return $response->getRpcResult();
    }
}
