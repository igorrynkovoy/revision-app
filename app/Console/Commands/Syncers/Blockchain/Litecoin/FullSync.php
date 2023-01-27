<?php

namespace App\Console\Commands\Syncers\Blockchain\Litecoin;

use Denpa\Bitcoin\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class FullSync extends Command
{
    private const SLEEP_ON_NO_BLOCK = 5;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ltc:full-sync';

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
        $fullSync = new \App\Services\Litecoin\Syncers\FullSync();

        while (true) {
            //$this->info('New while step.');

            $t = microtime(true);
            $block = $fullSync->getBlockToSync();

            if (!$block) {
                $this->noBlockFound();

            }
            $height =  Arr::get($block, 'height');
            $this->info('Block ' . $height . ' found and loaded in ' . (microtime(true) - $t));
            $fullSync->handleBlock($block);
            $this->info('Block ' . $height . ' saved in ' . (microtime(true) - $t));
        }
    }

    private function noBlockFound()
    {
        $this->info('No new block found. Sleep for a while');
        sleep(self::SLEEP_ON_NO_BLOCK);
    }
}
