<?php

namespace App\Services\DataServices\Blockchain\RemoteApp;

use GuzzleHttp\Client;
use function config;

class RemoteAPI
{
    const BLOCKCHAIN_LITECOIN = 'litecoin';

    private $defaultBlockchain;

    public function __construct(string $defaultBlockchain)
    {
        $url = config('services.remote-revision.host');
        $this->defaultBlockchain = $defaultBlockchain;

        $this->client = new Client([
            'base_uri' => $url,
            'timeout' => 15
        ]);
    }

    public function getAddressDetails($address)
    {
        $data = $this->request('GET', 'addresses/details/' . $address);
        return $data;
    }

    public function getAddressTransactions($address, $query = [])
    {
        return $this->request('GET', 'transactions/address/' . $address, null, ['query' => $query]);
    }

    protected function request($method, $path, $blockchain = null, $options = null)
    {
        $options = isset($options) ? $options : [];
        $defauls = [
            'query' => []
        ];

        $options = array_merge_recursive($defauls, $options);
        $blockchain = $blockchain ?? $this->defaultBlockchain;
        $path = '/api/blockchain/' . $blockchain . '/explorer/' . $path;
        $response = $this->client->request($method, $path, $options);

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data;
    }
}
