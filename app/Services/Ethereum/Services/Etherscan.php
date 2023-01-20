<?php

namespace App\Services\Ethereum\Services;

use Etherscan\APIConf;
use Illuminate\Support\Arr;

class Etherscan
{
    private $client;

    public function __construct()
    {
        $this->client = new \Etherscan\Client(config('services.etherscan.key'));
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getCurrentBlockNumber()
    {
        $block = $this->client->api('proxy')->blockNumber();
        $block = Arr::get($block, 'result', null);

        return empty($block) ? null : hexdec($block);
    }

    public function getLastTransaction($address)
    {
        $data = $this->client->api('account')->transactionListByAddress($address, 0, 99999999, 'desc', 1, 1);
        $data = Arr::first(Arr::get($data, 'result', []));
        return $data;
    }
}
