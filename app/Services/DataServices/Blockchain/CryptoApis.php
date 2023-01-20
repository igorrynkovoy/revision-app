<?php

namespace App\Services\DataServices\Blockchain;


use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use function config;

class CryptoApis
{
    const BLOCKCHAIN_LITECOIN = 'litecoin';
    const BLOCKCHAIN_ETHEREUM = 'ethereum';
    const API_HOST = 'https://rest.cryptoapis.io/blockchain-data/';

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

    public function listConfirmedTxsByAddress($address, $options = [])
    {
        $options = [
            'query' => $options
        ];

        $result = $this->request('GET', 'addresses/'.$address.'/transactions', null, $options);

        return $result;
    }

    protected function request($method, $path, $blockchain = null, $options = null)
    {
        $options = $options ?? [];
        $blockchain = $blockchain ?? $this->defaultBlockchain;
        $blockchainBranch = $this->blockchainBranch ? '/' . $this->blockchainBranch : '';
        Arr::set($options, 'headers.X-API-Key', config('services.cryptoapis.key'));
        $response = $this->client->request($method, $blockchain . $blockchainBranch . '/' . $path, $options);

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
