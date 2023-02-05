<?php

namespace App\Console\Commands\Syncers\Blockchain\Litecoin;

use App\Exceptions\Services\Sync\Blockchain\Litecoin\FullSync\BlockAlreadySynced;
use Denpa\Bitcoin\Client;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;

class FullSync extends Command
{
    private const SLEEP_ON_NO_BLOCK = 5;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ltc:full-sync {--from=} {--to=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Blockchain syncer';


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
        $optionCheck = (!isset($fromBlock) && !isset($toBlock)) || ((isset($fromBlock) || isset($toBlock)) && $fromBlock < $toBlock);

        if (!$optionCheck) {
            $this->error('Invalid period');
            exit;
        }

        $this->fullSync = new \App\Services\Litecoin\Syncers\FullSync();
        $nextBlockToSync = $fromBlock;
        $syncedBlockDetected = false;

        while (true) {
            if (isset($toBlock) && $nextBlockToSync >= $toBlock) {
                break;
            }

            $t = microtime(true);

            if ($syncedBlockDetected && $this->fullSync->isBlockSynced($nextBlockToSync)) {
                $this->warn('Block ' . $nextBlockToSync . ' has been already synced. Skip it.');
                $nextBlockToSync++;
                continue;
            } else {
                $syncedBlockDetected = false;
            }

            $block = $this->fullSync->getBlockToSync($nextBlockToSync);

            if (!$block) {
                $this->noBlockFound();
                continue;
            }

            $height = Arr::get($block, 'height');
            $txs = count($block['tx']);

            $this->info('Block ' . $height . ' found and loaded in ' . (microtime(true) - $t));

            try {
                $this->syncBlock($block);
                $t = (microtime(true) - $t);
                $method = $t > 0.1 ? 'warn' : 'info';
                $this->$method('Block ' . $height . ' with ' . $txs . ' txs saved in ' . $t);
            } catch (BlockAlreadySynced $exception) {
                $this->warn('Block ' . $height . ' has been already synced');
                $syncedBlockDetected = true;
            }

            $nextBlockToSync = $height + 1;


        }
    }

    private function syncBlock($block)
    {
        try {
            $this->fullSync->handleBlock($block);
        } catch (QueryException $exception) {
            if (strpos($exception->getMessage(), '1062 Duplicate entry') > 0) {
                throw new BlockAlreadySynced();
            }

            throw $exception;
        }
    }

    private function noBlockFound()
    {
        $this->info('No new block found. Sleep for a while');
        sleep(self::SLEEP_ON_NO_BLOCK);
    }
}
