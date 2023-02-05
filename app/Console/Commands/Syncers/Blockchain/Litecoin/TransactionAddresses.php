<?php

namespace App\Console\Commands\Syncers\Blockchain\Litecoin;

use App\Services\Litecoin\Syncers\TransactionAddressesSync;
use Denpa\Bitcoin\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TransactionAddresses extends Command
{
    private const SLEEP_ON_NO_BLOCK = 5;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ltc:tx-addresses {--from=} {--to=} {--chunk=10}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index address to transactions relations';


    protected Client $wallet;

    private \App\Services\Litecoin\Syncers\FullSync $fullSync;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $fromBlock = $this->option('from');
        $toBlock = $this->option('to');
        $chunk = (int)$this->option('chunk');

        $optionCheck = (!isset($fromBlock) && !isset($toBlock)) || ((isset($fromBlock) || isset($toBlock)) && $fromBlock < $toBlock);

        if (!$optionCheck) {
            $this->error('Invalid period');
            exit;
        }

        $syncer = new TransactionAddressesSync();

        $blockToSync = $fromBlock ?? $syncer->getLastSyncedBlockNumber() + 1;
        $toBlock = $toBlock ?? $syncer->getMaximumBlockNumber();
        while (true) {
            if (isset($toBlock) && $blockToSync > $toBlock) {
                break;
            }

            $t = microtime(true);

            $this->info(sprintf('Handle blocks from %s to %s', $blockToSync, $chunk));
            DB::beginTransaction();

            try {
                $syncer->syncBlock($blockToSync, $chunk);
            } catch (\Exception $exception) {
                DB::rollBack();
                throw $exception;
            }

            $this->info(sprintf('Blocks synced in %s', (microtime(true) - $t)));
            DB::commit();
            $blockToSync += $chunk;
        }
    }
}
