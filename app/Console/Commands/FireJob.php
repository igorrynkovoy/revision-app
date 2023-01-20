<?php

namespace App\Console\Commands;

use App\Jobs\Fire;
use App\Models\Blockchain\Ethereum;
use App\Services\Ethereum\Services\Etherscan;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class FireJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fire-job {address} {--fromBlock=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $etherScan;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $address = $this->argument('address');
        /** @var Ethereum\Address $ethAddress */
        $ethAddress = Ethereum\Address::query()->firstOrCreate(['address' => $address]);
        $this->etherScan = new Etherscan();
        $blockNumber = $this->etherScan->getCurrentBlockNumber();
        $this->info('Current block is ' . $blockNumber);
        //$tx = $this->etherScan->getLastTransaction($ethAddress->address);

        $fromBlock = $this->option('fromBlock') > 0 ? $this->option('fromBlock') : $ethAddress->synced_block_number;
        $this->info('Start sync from block ' . $fromBlock);
        do {
            $maxInsertTime = 0;
            $list = $this->getList($ethAddress->address, $fromBlock, $blockNumber);
            if (!is_array($list)) {
                dump($list);
                return;
            }
            $this->info('List loaded from block ' . $fromBlock . '. Rows: ' . count($list));

            foreach ($list as $txData) {
                $fromBlock = $txData['blockNumber'];
                try {
                    $t = microtime(true);
                    $this->saveTx($txData);
                    Ethereum\Address::where('address', $ethAddress->address)
                        ->update([
                            'synced_block_number' => $txData['blockNumber'],
                            'synced_transactions' => DB::raw('synced_transactions + 1'),
                            'last_sync_at' => DB::raw('NOW()')
                        ]);
                    $t = microtime(true) - $t;
                    $maxInsertTime = max($t, $maxInsertTime);
                    //$this->info('Transaction ' . $txData['hash'] . ' saved');
                } catch (QueryException $e) {
                    if ($e->errorInfo[1] === 1062) {
                        $this->warn('Transaction ' . $txData['hash'] . ' has been already saved');
                        continue;
                    }

                    throw $e;
                }
            }
            $this->info('Max isnert time is:' . $maxInsertTime);
        } while (count($list) >= 10000);
    }

    private function getLastTxBlock($address)
    {
        return DB::table('ethereum_transactions', 'tx')
            ->leftJoin('ethereum_transactions_addresses', 'tx.hash', '=', 'ethereum_transactions_addresses.transaction_hash')
            ->where('ethereum_transactions_addresses.address', $address)
            ->orderBy('tx.block_number', 'desc')
            ->limit(1)
            ->value('tx.block_number');
    }

    private function getList($address, $fromBlock, $toBlock)
    {
        $list = $this->etherScan->getClient()
            ->api('account')
            ->transactionListByAddress($address, $fromBlock, $toBlock, 'asc', 1, 10000);

        return Arr::get($list, 'result', []);
    }


}
