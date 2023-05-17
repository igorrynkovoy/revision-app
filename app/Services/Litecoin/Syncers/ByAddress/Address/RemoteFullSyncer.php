<?php

namespace App\Services\Litecoin\Syncers\ByAddress\Address;

use App\Events\Blockchain\Litecoin\Address\Updated;
use App\Models\Blockchain\Litecoin;
use App\Services\DataServices\Blockchain\RemoteApp\RemoteAPI;
use App\Services\Litecoin\BlockchainLitecoin;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class RemoteFullSyncer
{
    protected $address;
    private int $currentPage = 1;
    private int $sinceBlock;
    protected RemoteAPI $remoteAPI;
    private BlockchainLitecoin $blockchain;

    public function __construct(Litecoin\Address $address)
    {
        $this->address = $address;

        $this->blockchain = new BlockchainLitecoin();

        if ($this->blockchain->getSyncMode() === BlockchainLitecoin::SYNC_MODE_FULL) {
            throw new \RuntimeException('Cannot use this class in full sync mode');
        }

        $this->remoteAPI = $this->blockchain->getRemoteAPI();
    }

    public function sync()
    {
        $this->sinceBlock = $this->address->synced_block_number > 0 ? $this->address->synced_block_number : $this->address->blockchain_first_tx_block;

        do {
            DB::beginTransaction();
            $break = $this->syncStep();
            DB::commit();

            $this->address->refresh();

            // TODO: Может удалить? Создает доп нагрузку. Но зато видно прогресс при remote синке.
            event(new Updated($this->address));

            dump(sprintf(
                'Last synced block: %s. Last TX: %s. Total TXs: %s.',
                $this->address->synced_block_number,
                $this->address->last_transaction_hash,
                $this->address->synced_transactions
            ));
        } while (!$break);
    }

    private function syncStep()
    {
        $t = microtime(true);
        $perPage = 50;

        dump(sprintf('Sync %s since block %s, page %s, limit %s', $this->address->address, $this->sinceBlock, $this->currentPage, $perPage));

        $list = $this->getTxs($this->address->address, $this->sinceBlock, $this->currentPage, $perPage);

        if (empty($list)) {
            dump('End list finished');
            return true;
        }

        $savedTransactions = 0;

        foreach ($list as $txData) {
            $lastBlockNumber = (int)$txData['block_number'];
            $lastTxHash = $txData['hash'];
            try {
                $savedTransactions++;
                $this->saveTx($txData);
            } catch (QueryException $e) {
                if ($e->errorInfo[1] === 1062) {
                    //dump('Tx has duplicated: ' . $txData['hash']);
                    continue;
                }

                throw $e;
            }
        }

        dump(sprintf('Saved %s. Last block is: %s. Duration: %s', $savedTransactions, $lastBlockNumber, (microtime(true) - $t)));

        $update = [
            'synced_block_number' => $lastBlockNumber,
            'synced_transactions' => DB::raw('(select count(*) as cnt FROM litecoin_transactions_addresses where address = "' . $this->address->address . '")'),
            'last_transaction_hash' => $lastTxHash,
            'last_sync_at' => DB::raw('NOW()')
        ];

        Litecoin\Address::where('address', $this->address->address)
            ->update($update);

        if (count($list) === $perPage) {
            if (false && $lastBlockNumber !== $this->sinceBlock) {
                // Improve API call to reduce high offsets and filter by block number
                $this->currentPage = 1;
                $this->sinceBlock = $lastBlockNumber;
            } else {
                $this->currentPage++;
            }

            return false;
        }

        return true;
    }

    protected function getTxs($address, $sinceBlock, $page, $limit): array
    {
        $list = $this->remoteAPI->getAddressTransactions($address, [
            'since_block' => $sinceBlock,
            'page' => $page,
            'limit' => $limit,
            'with_outputs' => 1
        ]);

        return \Arr::get($list, 'data');
    }

    protected function saveTx(array $tx)
    {
        $transaction = new Litecoin\Transaction();
        $transaction->hash = $tx['hash'];
        $transaction->block_number = $tx['block_number'];
        $transaction->is_coinbase = $tx['is_coinbase'];
        $transaction->total_inputs = $tx['total_inputs'];
        $transaction->total_outputs = $tx['total_outputs'];
        $transaction->amount = $tx['amount'];
        $transaction->fee = 0;
        $transaction->created_at = Carbon::createFromTimestampUTC($tx['created_at']);
        $transaction->added_at = Carbon::now();

        $transaction->save();
        $addresses = [];
        foreach ($tx['inputs'] as $index => $input) {
            Litecoin\TransactionOutput::query()->upsert([
                'address' => $input['address'],
                'transaction_hash' => $input['transaction_hash'],
                'index' => $input['index'],
                'input_transaction_hash' => $input['input_transaction_hash'],
                'input_index' => $input['input_index'],
                'value' => $input['value'],
                'script_type' => $input['script_type'],
                'block_number' => $input['block_number'],
            ], ['index', 'transaction_hash'], ['input_transaction_hash', 'input_index']);
            $addresses[] = $input['address'];
        }

        foreach ($tx['outputs'] as $output) {
            Litecoin\TransactionOutput::query()->upsert([
                'address' => $output['address'],
                'transaction_hash' => $output['transaction_hash'],
                'index' => $output['index'],
                'input_transaction_hash' => $output['input_transaction_hash'],
                'input_index' => $output['input_index'],
                'value' => $output['value'],
                'script_type' => $output['script_type'],
                'block_number' => $output['block_number']
            ], ['index', 'transaction_hash']);
            $addresses[] = $output['address'];
        }
        $addresses = array_unique($addresses);
        $addresses = array_map(function ($a) use ($tx) {
            return ['address' => $a, 'transaction_hash' => $tx['hash'], 'block_number' => $tx['block_number']];
        }, $addresses);

        DB::table('litecoin_transactions_addresses')->insertOrIgnore($addresses);

        return $transaction;
    }

}
