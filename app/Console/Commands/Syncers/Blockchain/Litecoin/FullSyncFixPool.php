<?php

namespace App\Console\Commands\Syncers\Blockchain\Litecoin;

use App\Models\Blockchain\Ethereum\TransactionOutput;
use Denpa\Bitcoin\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class FullSyncFixPool extends Command
{
    private const SLEEP_ON_NO_BLOCK = 5;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ltc:full-sync-fix-pool';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    protected Client $wallet;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $lastChunkBlock = 0;
        while (true) {
            $items = DB::table('litecoin_transactions_inputs_pool')
                ->select(['litecoin_transactions_inputs_pool.*'])
                ->leftJoin('litecoin_transaction_outputs', function ($join) {
                    $join->on('litecoin_transactions_inputs_pool.output_transaction_hash', '=', 'litecoin_transaction_outputs.transaction_hash');
                    $join->on('litecoin_transactions_inputs_pool.output_index', '=', 'litecoin_transaction_outputs.index');
                })
                ->whereNotNull('litecoin_transaction_outputs.id')
                ->orderBy('input_block_number', 'asc')
                ->limit(10)
                ->get();
                dump($items);
            if($items->isEmpty()) {
                break;
            }

            foreach ($items as $item) {
                $item = (array)$item;
                DB::beginTransaction();
                dump($item);
                $updated = \App\Models\Blockchain\Litecoin\TransactionOutput::query()->getQuery()
                    ->where('transaction_hash', $item['output_transaction_hash'])
                    ->where('index', $item['output_index'])
                    ->update([
                        'input_transaction_hash' => $item['input_transaction_hash'],
                        'input_index' => $item['input_index']
                    ]);

                if ($updated) {
                    DB::table('litecoin_transactions_inputs_pool')
                        ->where('input_transaction_hash', $item['input_transaction_hash'])
                        ->where('input_index', $item['input_index'])
                        ->delete();
                    dump('Deleted');
                }
                DB::commit();
            }

        }
    }

    private function noBlockFound()
    {

    }
}
