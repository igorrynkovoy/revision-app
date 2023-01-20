<?php

namespace App\Services\Ethereum\Syncers;

use App\Models\Blockchain\Ethereum;
use App\Models\Graph\Ethereum as EthereumGraph;
use App\Services\DataServices\Blockchain\BlockCypher;
use App\Services\Ethereum\Services\Etherscan;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AddressSyncer
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

    public function syncInformation()
    {
        $data = $this->blockCypher->getAddress($this->address->address, ['limit' => 1, 'confirmations' => 1]);
        $this->address->blockchain_transactions = Arr::get($data, 'final_n_tx', NULL);
        $this->address->blockchain_last_tx_block = Arr::get($data, 'txrefs.0.block_height', NULL);
        $this->address->blockchain_data_updated_at = Carbon::now();
        $this->address->save();
    }

    public function sync()
    {
        if($this->address->blockchain_last_tx_block === $this->address->synced_block_number) {
            return;
        }

        if($this->address->blockchain_transactions > 30000) {
            throw new \RuntimeException('Address ' . $this->address->address . ' has too many transactions. Total: ' . $this->address->blockchain_transactions);
        }

        do {
            DB::beginTransaction();
            $result = $this->syncStep('latest');
            DB::commit();

            $this->address->refresh();

            dump('Last block synced: ' . $this->address->synced_block_number . ' Last TX: ' . $this->address->last_transaction_hash . ' Total TXs: ' . $this->address->synced_transactions);
        } while ($result);

    }


    public function syncStep($toBlock)
    {
        $perPage = 10000;
        $list = $this->getList($this->address->address, $this->address->synced_block_number, $toBlock, $perPage);
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

        return $lastBlockNumber < $this->address->blockchain_last_tx_block;
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

        $this->saveNeoTx($transaction);

        return $transaction;
    }

    private function saveNeoTx(Ethereum\Transaction $tx)
    {
        $transaction = EthereumGraph\Transaction::firstOrNew(['hash' => $tx->hash]);
        if(!$transaction->exists) {
            $transaction->blockNumber = $tx->block_number;
            $transaction->gasUsage = $tx->gas_usage;
            $transaction->gasPrice = $tx->gas_price;
            $transaction->save();
        }

        $fromNodes = [];
        foreach ($tx->inputs as $input) {
            $from = EthereumGraph\Address::firstOrCreate([
                'address' => $input->address,
                'type' => 'address'
            ]);
            $transaction->in()->attach($from, ['amount' => $input->value]);
            $fromNodes[] = $from;
        }

        foreach ($tx->outputs as $output) {
            $to = EthereumGraph\Address::firstOrCreate([
                'address' => $output->address,
                'type' => 'address'
            ]);
            $transaction->out()->attach($to, ['amount' => $output->value]);
            foreach ($fromNodes as $from) {
                $edge = $to->transfers()->attach($from);
                $edge->count = $edge->count + 1;
                $edge->amount = $edge->amount + $output->value;
                $edge->save();
            }
        }
    }

    private function getList($address, $fromBlock, $toBlock, $limit): array
    {
        $t = microtime(true);
        $list = $this->etherScan->getClient()
            ->api('account')
            ->transactionListByAddress($address, $fromBlock, $toBlock, 'asc', 1, $limit);

        $result = Arr::get($list, 'result', []);

        if (is_string($result)) {
            dump($result);
        }

        return $result;
    }
}
