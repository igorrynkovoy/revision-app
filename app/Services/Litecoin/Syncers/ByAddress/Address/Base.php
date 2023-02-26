<?php

namespace App\Services\Litecoin\Syncers\ByAddress\Address;

use App\Models\Blockchain\Litecoin;
use App\Services\DataServices\Blockchain\BlockCypher;
use App\Services\DataServices\Blockchain\RemoteApp\RemoteAPI;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class Base
{
    protected $address;
    protected BlockCypher $blockCypher;
    protected RemoteAPI $remoteAPI;
    protected $lastSyncBlock;

    public function __construct(Litecoin\Address $address)
    {
        $this->address = $address;
        $this->blockCypher = new BlockCypher('ltc');
        $this->remoteAPI = new RemoteAPI(RemoteAPI::BLOCKCHAIN_LITECOIN);
    }

    protected function getTxs($address, $sinceBlock, $page, $limit): array
    {
        $list = $this->remoteAPI->getAddressTransactions($address, [
            'since_block' => $sinceBlock,
            'page' => $page,
            'limit' => $limit,
            'with_outputs' => 1
        ]);

        return $list;
    }

    protected function getList($address, $beforeBlock, $afterBlock, $limit): array
    {
        $t = microtime(true);
        dump('Get list', func_get_args());

        $list = $this->blockCypher->getAddressFull($address, [
            'before' => $beforeBlock,
            'after' => $afterBlock,
            'limit' => $limit,
            'txlimit' => 500
        ]);

        $result = Arr::get($list, 'txs', []);

        if (is_string($result)) {
            dump($result);
        }

        return $result;
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
        }

        $addresses = array_map(function ($a) use ($tx) {
            return ['address' => $a, 'transaction_hash' => $tx['hash'], 'block_number' => $tx['block_number']];
        }, $tx['addresses']);

        DB::table('litecoin_transactions_addresses')->insertOrIgnore($addresses);

        return $transaction;
    }
}
