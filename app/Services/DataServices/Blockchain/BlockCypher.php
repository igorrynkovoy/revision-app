<?php

namespace App\Services\DataServices\Blockchain;


use GuzzleHttp\Client;
use Illuminate\Support\Arr;

class BlockCypher
{
    protected $client;

    protected $defaultBlockchain;

    protected $blockchainBranch = 'main';

    public function __construct(string $defaultBlockchain)
    {
        $this->client = new Client([
            'base_uri' => 'https://api.blockcypher.com/v1/',
            'timeout' => 15
        ]);

        $this->defaultBlockchain = $defaultBlockchain;
    }

    public function setDefaultBlockchain(string $defaultBlockchain)
    {
        $this->defaultBlockchain = $defaultBlockchain;
    }

    public function getTransaction($txHash, $options = [])
    {
        $options = [
            'query' => $options
        ];

        $result = $this->request('GET', 'txs/' . $txHash, null, $options);

        return $result;
    }

    public function getAddress($address, $options = [])
    {
        $options = [
            'query' => $options
        ];

        $result = $this->request('GET', 'addrs/' . $address, null, $options);

        return $result;
    }

    public function getAddressFull($address, $options = [])
    {
        $options = [
            'query' => $options
        ];

        $result = $this->request('GET', 'addrs/' . $address . '/full', null, $options);

        return $result;
    }

    public function getBalance($address)
    {
        $balance = $this->request('GET', 'addrs/' . $address . '/balance');

        return $balance;
    }

    public function getBalances(array $addresses)
    {
        $addresses = implode(';', $addresses);

        $balances = $this->request('GET', 'addrs/' . $addresses . '/balance');

        return Arr::pluck($balances, 'balance', 'address');
    }

    protected function request($method, $path, $blockchain = null, $options = null)
    {
        $options = isset($options) ? $options : [];
        $defauls = [
            'query' => ['token' => '01105ff626544b4394019a7ddc3bfe2e']
        ];
        $options = array_merge_recursive($defauls, $options);
        $blockchain = isset($blockchain) ? $blockchain : $this->defaultBlockchain;

        $response = $this->client->request($method, $blockchain . '/' . $this->blockchainBranch . '/' . $path, $options);

        $data = $response->getBody()->getContents();
        $data = json_decode($data, true);

        return $data;
    }
}
