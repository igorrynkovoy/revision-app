<?php

namespace App\Console\Commands;

use App\Events\DepthSync\Created;
use App\Events\DepthSync\Updated;
use App\Jobs\Blockchain\Litecoin\SyncAddress;
use App\Services\Litecoin\Syncers\ByAddress\AddressSyncer;
use App\Models\Blockchain;
use Illuminate\Console\Command;

class Play extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'play';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $address = 'MATH1RbJUVYg6BGnQDWiBngeh9JURcV4da';
        $this->runSync($address);
    }

    private function runJob($address)
    {
        dispatch(new SyncAddress($address));
    }

    private function runSync($address)
    {
        /** @var Blockchain\Litecoin\Address $address */
        $address = Blockchain\Litecoin\Address::firstOrCreate(['address' => $address]);
        $syncer = new AddressSyncer($address);
        $syncer->syncInformation();
        $syncer->sync();
    }
}
