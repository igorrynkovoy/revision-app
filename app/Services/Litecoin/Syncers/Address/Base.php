<?php

namespace App\Services\Litecoin\Syncers\Address;

use App\Models\Blockchain\Litecoin;
use App\Services\DataServices\Blockchain\BlockCypher;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class Base
{
    protected $address;
    protected BlockCypher $blockCypher;

    public function __construct(Litecoin\Address $address)
    {
        $this->address = $address;
        $this->blockCypher = new BlockCypher('ltc');
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
        $transaction->block_hash = $tx['block_hash'];
        $transaction->block_number = $tx['block_height'];
        $transaction->total_inputs = $tx['vin_sz'];
        $transaction->total_outputs = $tx['vout_sz'];
        $transaction->fee = $tx['fees'];
        $transaction->amount = $tx['total'];
        $transaction->created_at = Carbon::createFromTimeString($tx['confirmed']);

        $transaction->save();

        foreach ($tx['inputs'] as $index => $input) {
            Litecoin\TransactionOutput::query()->upsert([
                'address' => $input['addresses'][0],
                'transaction_hash' => $input['prev_hash'],
                'index' => $input['output_index'],
                'value' => $input['output_value'],
                'script_type' => $input['script_type'],
                'block_number' => $input['age'],
                'input_transaction_hash' => $tx['hash'],
                'input_index' => $index
            ], ['index', 'transaction_hash'], ['input_transaction_hash', 'input_index']);


        }

        foreach ($tx['outputs'] as $index => $output) {
            Litecoin\TransactionOutput::query()->upsert([
                'address' => $output['addresses'][0],
                'transaction_hash' => $tx['hash'],
                'index' => $index,
                'value' => $output['value'],
                'script_type' => $output['script_type'],
                'block_number' => $tx['block_height'],
                'created_at' => $transaction->created_at
            ], ['index', 'transaction_hash']);
        }

        $addresses = array_map(function ($a) use ($tx) {
            return ['address' => $a, 'transaction_hash' => $tx['hash']];
        }, $tx['addresses']);

        DB::table('litecoin_transactions_addresses')->insertOrIgnore($addresses);

        return $transaction;
    }
}
