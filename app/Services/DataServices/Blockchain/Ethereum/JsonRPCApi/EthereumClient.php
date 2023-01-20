<?php

namespace App\Services\DataServices\Blockchain\Ethereum\JsonRPCApi;

use App\Services\DataServices\Blockchain\Ethereum\JsonRPCApi\Methods\Eth;
use App\Services\DataServices\Blockchain\Ethereum\JsonRPCApi\Methods\Net;
use App\Services\DataServices\Blockchain\Ethereum\JsonRPCApi\Methods\Personal;
use App\Services\DataServices\Blockchain\Ethereum\JsonRPCApi\Methods\Shh;
use App\Services\DataServices\Blockchain\Ethereum\JsonRPCApi\Methods\Trace;
use App\Services\DataServices\Blockchain\Ethereum\JsonRPCApi\Methods\Web3;
use Graze\GuzzleHttp\JsonRpc\Client;

class EthereumClient
{
    private $client;
    private $methods = [];

    public function __construct($url)
    {
        $this->client = Client::factory($url);
        $this->methods = [
            'net' => new Net($this->client),
            'eth' => new Eth($this->client),
            'shh' => new Shh($this->client),
            'web3' => new Web3($this->client),
            'trace' => new Trace($this->client),
            'personal' => new Personal($this->client),
        ];
    }

    public function disableLogger()
    {
        foreach ($this->methods as $methodClass) {
            $methodClass->disableLogs();
        }
    }

    public function net(): Net
    {
        return $this->methods['net'];
    }

    public function web3(): Web3
    {
        return $this->methods['web3'];
    }

    public function shh(): Shh
    {
        return $this->methods['shh'];
    }

    public function eth(): Eth
    {
        return $this->methods['eth'];
    }

    public function trace(): Trace
    {
        return $this->methods['trace'];
    }

    public function personal(): Personal
    {
        return $this->methods['personal'];
    }
}
