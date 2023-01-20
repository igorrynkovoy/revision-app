<?php

namespace App\Services\DataServices\Blockchain\Ethereum\JsonRPCApi\Methods;

use App\Services\DataServices\Blockchain\Ethereum\JsonRPCApi\Types\Address;
use App\Services\DataServices\Blockchain\Ethereum\JsonRPCApi\Types\Transaction;
use App\Services\DataServices\Blockchain\Ethereum\JsonRPCApi\Types\TransactionHash;

class Personal extends AbstractMethods
{
    /**
     * @return Address[]
     */
    public function listAccounts(): array
    {
        $addresses = [];
        $response = $this->send(
            $this->client->request(67, 'personal_listAccounts', [])
        );

        if (!$response->getRpcResult()) {
            return $addresses;
        }
        foreach ($response->getRpcResult() as $address) {
            $addresses[] = new Address($address);
        }

        return $addresses;
    }

    public function newAccount(string $password): Address
    {
        $response = $this->send(
            $this->client->request(67, 'personal_newAccount', [$password])
        );

        return new Address($response->getRpcResult());
    }

    public function unlockAccount(Address $address, string $password, int $duration): bool
    {
        $response = $this->send(
            $this->client->request(67, 'personal_unlockAccount', [$address->toString(), $password, $duration])
        );

        $result = $response->getRpcResult();

        return empty($result) ? false : true;
    }

    public function sendTransaction(Transaction $transaction, string $password): ?TransactionHash
    {
        $response = $this->send(
            $this->client->request(1, 'personal_sendTransaction', [$transaction->toArray(), $password])
        );

        $result = $response->getRpcResult();

        return !empty($result) ? new TransactionHash($result) : null;

    }
}
