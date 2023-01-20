<?php

namespace App\Services\DataServices\Blockchain\Ethereum\JsonRPCApi\Types;

use App\Exceptions\Crypto\Ethereum\EmptyBlockHashException;

class TransactionReceipt
{
    private $blockHash;
    private $blockNumber;
    private $contractAddress;
    private $cumulativeGasUsed;
    private $gasUsed;
    private $from;
    private $to;
    private $hash;
    private $transactionIndex;
    private $status;

    public function __construct(array $response)
    {
        if(!isset($response['blockHash']) || empty($response['blockHash'])) {
            throw new EmptyBlockHashException();
        }

        $this->blockHash = new BlockHash($response['blockHash']);
        $this->blockNumber = hexdec($response['blockNumber']);

        if ($response['contractAddress']) {
            $this->contractAddress = new Address($response['contractAddress']);
        }

        $this->cumulativeGasUsed = new Wei(hexdec($response['cumulativeGasUsed']));
        $this->gasUsed = new Wei(hexdec($response['gasUsed']));

        $this->from = new Address($response['from']);

        if ($response['to']) {
            $this->to = new Address($response['to']);
        }

        $this->hash = new TransactionHash($response['transactionHash']);
        $this->transactionIndex = hexdec($response['transactionIndex']);
        $this->status = hexdec($response['status']);
    }

    public function blockHash(): BlockHash
    {
        return $this->blockHash;
    }

    public function blockNumber(): int
    {
        return $this->blockNumber;
    }

    public function contractAddress(): ?Address
    {
        return $this->contractAddress;
    }

    public function cumulativeGasUsed(): Wei
    {
        return $this->cumulativeGasUsed;
    }

    public function gasUsed(): Wei
    {
        return $this->gasUsed;
    }

    public function from(): Address
    {
        return $this->from;
    }

    public function to(): ?Address
    {
        return $this->to;
    }

    public function hash(): TransactionHash
    {
        return $this->hash;
    }

    public function transactionIndex(): int
    {
        return $this->transactionIndex;
    }

    public function status(): int
    {
        return $this->status;
    }
}
