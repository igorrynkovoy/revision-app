<?php

namespace App\Services\Ethereum\Jobs;

use App\Models\Blockchain\Ethereum;
use App\Services\DataServices\Blockchain\BlockCypher;
use App\Services\Ethereum\Services\Etherscan;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SyncAddress
{
    protected Ethereum\Address $address;

    private Etherscan $etherScan;
    private BlockCypher $blockCypher;

    public function __construct(Ethereum\Address $address)
    {
        $this->address = $address;
        $this->etherScan = new Etherscan();
        $this->blockCypher = new BlockCypher('eth');
    }

    public function getEtherscan()
    {
        return $this->etherScan;
    }

    public function getAddress(): Ethereum\Address
    {
        return $this->address;
    }

    public function refreshAddress()
    {
        $this->address = $this->address->fresh();

        return $this->address;
    }

    public function getBlockchainTransactions(): int
    {
        $data = $this->blockCypher->getAddress($this->address->address, ['limit' => 0]);

        return (int)Arr::get($data, 'final_n_tx', 0);
    }

    public function syncStep(int $toBlock)
    {
        $list = $this->getList($this->address->address, $this->address->synced_block_number, $toBlock);
        $lastBlockNumber = null;
        $lastTransactionHash = null;
        $savedTransactions = 0;

        if (count($list) === 0) {
            return false;
        }

        foreach ($list as $txData) {
            $lastBlockNumber = (int)$txData['blockNumber'];
            $lastTransactionHash = $txData['hash'];

            try {
                $this->saveTx($txData);
                $savedTransactions++;
            } catch (QueryException $e) {
                if ($e->errorInfo[1] === 1062) {
                    continue;
                }

                throw $e;
            }
        }

        Ethereum\Address::where('address', $this->address->address)
            ->update([
                'synced_block_number' => $lastBlockNumber,
                'last_transaction_hash' => $lastTransactionHash,
                'synced_transactions' => DB::raw('synced_transactions + ' . $savedTransactions),
                'last_sync_at' => DB::raw('NOW()')
            ]);

        return $lastBlockNumber !== $this->address->synced_block_number;
    }

    private function saveTx(array $tx)
    {
        $transaction = new Ethereum\Transaction();
        $transaction->hash = $tx['hash'];
        $transaction->block_hash = $tx['blockHash'];
        $transaction->block_number = $tx['blockNumber'];
        $transaction->gas_usage = $tx['gasUsed'];
        $transaction->gas_price = $tx['gasPrice'];
        $transaction->total_input = $tx['value'];
        $transaction->total_output = $tx['value'];
        $transaction->created_at = Carbon::createFromTimestamp($tx['timeStamp']);
        $transaction->save();

        $transactionInput = new Ethereum\TransactionInput();
        $transactionInput->address = $tx['from'];
        $transactionInput->address_type = 'address';
        $transactionInput->transaction_hash = $transaction->hash;
        $transactionInput->index = 0;
        $transactionInput->value = $tx['value'];
        $transactionInput->save();

        $transactionOutput = new Ethereum\TransactionOutput();
        $transactionOutput->address = $tx['to'];
        $transactionOutput->address_type = 'address';
        $transactionOutput->transaction_hash = $transaction->hash;
        $transactionOutput->index = 0;
        $transactionOutput->value = $tx['value'];
        $transactionOutput->value_type = 'ETH';
        $transactionOutput->type = 'normal';
        $transactionOutput->save();

        $addresses = array_unique([$transactionInput->address, $transactionOutput->address]);
        foreach ($addresses as $address) {
            DB::table('ethereum_transactions_addresses')->insert(['address' => $address, 'transaction_hash' => $tx['hash']]);
        }

        return $transaction;
    }

    private function getList($address, $fromBlock, $toBlock): array
    {
        $t = microtime(true);
        $list = $this->etherScan->getClient()
            ->api('account')
            ->transactionListByAddress($address, $fromBlock, $toBlock, 'asc', 1, 10000);

        $result = Arr::get($list, 'result', []);

        if(is_string($result)) {
            dump($result);
        }

        return $result;
    }


}
