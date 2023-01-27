<?php

namespace App\Console\Commands\Syncers\Blockchain\Litecoin;

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
    protected $description = 'Command description';


    protected Client $wallet;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $fromBlock = $this->option('from');
        $toBlock = $this->option('to');
        $optionCheck = ((isset($fromBlock) || isset($toBlock)) && $fromBlock < $toBlock);

        if (!$optionCheck) {
            $this->error('Invalid period');
            exit;
        }

        $fullSync = new \App\Services\Litecoin\Syncers\FullSync();
        $nextBlockToSync = $fromBlock;
        while (true) {
            //$this->info('New while step.');

            $t = microtime(true);
            $block = $fullSync->getBlockToSync($nextBlockToSync);

            if (!$block) {
                $this->noBlockFound();
            }
            $height = Arr::get($block, 'height');
            $txs = count($block['tx']);

            $this->info('Block ' . $height . ' found and loaded in ' . (microtime(true) - $t));
            try {
                $fullSync->handleBlock($block);
                $t = (microtime(true) - $t);
                $method = $t > 0.1 ? 'warn' : 'info';
                $this->$method('Block ' . $height . ' with ' . $txs . ' txs saved in ' . $t);
            } catch (QueryException $exception) {
                if (strpos($exception->getMessage(), '1062 Duplicate entry') > 0) {
                    $this->warn('Block has been already processed');
                    $nextBlockToSync = $height + 1;
                    continue;
                }
                throw $exception;
            }
            
            $nextBlockToSync = $height + 1;

            if (isset($toBlock) && $nextBlockToSync >= $toBlock) {
                break;
            }
        }
    }

    private function noBlockFound()
    {
        $this->info('No new block found. Sleep for a while');
        sleep(self::SLEEP_ON_NO_BLOCK);
    }
}
