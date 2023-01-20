<?php

namespace App\Console\Commands;

use App\Models\Visor\Address;
use Illuminate\Console\Command;

class AddAddress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:add-address';

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
        $address = Address::query()->firstOrCreate(['type' => 'Ethereum', 'address' => '0x8924da29eeaaaef281454d588d9e570ef090f3a0']);

        dispatch(new \App\Jobs\AddAddress($address));
    }
}
