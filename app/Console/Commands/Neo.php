<?php

namespace App\Console\Commands;

use App\Models\Blockchain\Ethereum;
use App\Models\Graph\Ethereum\Address;
use App\Models\Graph\Ethereum\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class Neo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'neo';

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
        Ethereum\Transaction::with(['inputs', 'outputs'])->chunk(100, function (Collection $collection) {
            $collection->each(function (Ethereum\Transaction $transaction) {
                $this->info('Save ' . $transaction->hash);
                $this->saveTx($transaction);
            });
        });
        dd();
        $from = Address::firstOrCreate(['address' => '0xadee0d9485820c6d099deb0b09e312639e665c84', 'type' => 'address']);
        $to = Address::firstOrCreate(['address' => '0x912fd21d7a69678227fe6d08c64222db41477ba0', 'type' => 'address']);


        $transaction = Transaction::firstOrNew(['hash' => '0x3b3ce9e5fd356cdec4380a3207c5de8d33571e0b70a04b8a005e852ca7a44b3d']);
        if (!$transaction->exists) {
            $transaction->hash = '0x3b3ce9e5fd356cdec4380a3207c5de8d33571e0b70a04b8a005e852ca7a44b3d';
            $transaction->blockNumber = 15083957;
            $transaction->gasUsage = 21000;
            $transaction->gasPrice = 0.000000020387304818;
            $transaction->save();
        }
        $transaction->in()->attach($from, ['amount' => 135416764491346912]);
        $transaction->out()->save($to, ['amount' => 135416764491346912]);
    }

    private function saveTx(Ethereum\Transaction $tx)
    {
        $transaction = Transaction::firstOrNew(['hash' => $tx->hash]);
        if (!$transaction->exists) {
            $transaction->blockNumber = $tx->block_number;
            $transaction->gasUsage = $tx->gas_usage;
            $transaction->gasPrice = $tx->gas_price;
            $transaction->save();
        }

        foreach ($tx->inputs as $input) {
            $from = Address::firstOrCreate([
                'address' => $input->address,
                'type' => 'address'
            ]);

            $transaction->in()->attach($from, ['amount' => $input->value]);
            $this->info('Attach in ' . $input->address);
        }

        foreach ($tx->outputs as $output) {
            $from = Address::firstOrCreate([
                'address' => $output->address,
                'type' => 'address'
            ]);
            $transaction->out()->attach($from, ['amount' => $output->value]);
            $this->info('Attach out ' . $output->address);
        }
    }
}
