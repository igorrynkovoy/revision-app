<?php

namespace App\Services\DataServices\Blockchain;


use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use function config;

class BlockChair
{
    const BLOCKCHAIN_LITECOIN = 'litecoin';
    const BLOCKCHAIN_ETHEREUM = 'ethereum';
    const API_HOST = 'https://api.blockchair.com/';

    protected $client;

    protected $defaultBlockchain;

    protected $blockchainBranch;

    public function __construct(string $defaultBlockchain, string $blockchainBranch = null)
    {
        $this->client = new Client([
            'base_uri' => static::API_HOST,
            'timeout' => 10
        ]);

        $this->defaultBlockchain = $defaultBlockchain;
        $this->blockchainBranch = $blockchainBranch;
    }

    public function setDefaultBlockchain(string $defaultBlockchain)
    {
        $this->defaultBlockchain = $defaultBlockchain;
    }

    public function getAddress($address, $options = [])
    {
        $options = [
            'query' => $options
        ];

        $result = $this->request('GET', 'dashboards/address/' . $address, null, $options);

        return $result;
    }

    public function transactionConfirmations($txHash, $options = [])
    {
        $options = [
            'query' => $options
        ];

        $result = $this->request('GET', 'dashboards/transaction/' . $txHash, null, $options);

        $txBlockId = Arr::get($result, 'data.' . $txHash . '.transaction.block_id');
        if (empty($txBlockId)) {
            return 0;
        }

        return Arr::get($result, 'context.state') - Arr::get($result, 'data.' . $txHash . '.transaction.block_id') + 1;
    }

    public function getTransaction($txHash, $options = [])
    {
        $options = [
            'query' => $options
        ];

        $result = $this->request('GET', 'dashboards/transaction/' . $txHash, null, $options);

        return Arr::get($result, 'data.' . $txHash);
    }

    protected function request($method, $path, $blockchain = null, $options = null)
    {
        $options = isset($options) ? $options : [];
        $blockchain = isset($blockchain) ? $blockchain : $this->defaultBlockchain;

        Arr::set($options, 'query.key', config('services.blockchair.key'));

        $response = $this->client->request($method, $blockchain . '/' . ($this->blockchainBranch ? '/' . $this->blockchainBranch : '') . $path, $options);

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data;
    }

    public static function getAPIStatus()
    {
        $options = [
            'query' => ['key' => config('services.blockchair.key')]
        ];

        $client = new Client([
            'base_uri' => static::API_HOST,
            'timeout' => 5
        ]);

        $response = $client->request("GET", 'premium/stats', $options);

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return Arr::get($data, 'data');
    }
}
