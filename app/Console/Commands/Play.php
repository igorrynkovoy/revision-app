<?php

namespace App\Console\Commands;

use App\Exceptions\Services\Sync\Blockchain\Litecoin\AddressSyncer\AddressNotFound;
use App\Services\Sync\DepthSync;
use App\Models\Blockchain;
use App\Services\Sync\DepthSync\Service;
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
        $destinationAddress = '3HnkejzGjAYzRuUt7o2sKRxuba2UKXMjdn';
        echo "SRC: " . $destinationAddress . PHP_EOL;

        Bitcoin::setNetwork(NetworkFactory::bitcoin());

        $addressCreator = new AddressCreator();
        $address = $addressCreator->fromString($destinationAddress);

        $p2pkh = new PayToPubKeyHashAddress($address->getHash());

        $p2wpkhWP1 = WitnessProgram::v0($p2pkh->getHash());
        $p2shP2wsh1 = new ScriptHashAddress($p2wpkhWP1->getScript()->getScriptHash());

        echo "DST: " . $p2shP2wsh1->getAddress() . PHP_EOL;
    }

    public function addressSynces()
    {
        $addressValue = 'ltc1qw9wk5gcjdvqvt5glm3m3f4k39sqs2s0wgp37hj';
        $address = Blockchain\Litecoin\Address::firstOrCreate(['address' => $addressValue]);

        if (!$address) {
            throw new AddressNotFound('Address ' . $addressValue . ' not found.');
        }

        $depthOnSync = new DepthSync\OnAddressSynced($address);
        $depthOnSync->markDepthSyncAddresses();
        $depthOnSync->continueDepthSyncs();
    }

    public function runRoot()
    {

        /** @var \App\Models\Blockchain\DepthSync $rootSync */
        $rootSync = Blockchain\DepthSync::find(318);

        $service = new Service();
        $service->handleRootOnDepth($rootSync, 4);

        return;
    }

}
