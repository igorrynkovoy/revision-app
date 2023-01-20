<?php

namespace App\Console\Commands;

use App\Models\Blockchain;
use App\Services\DataServices\Blockchain\BlockCypher;
use App\Services\DataServices\Blockchain\CryptoApis;
use App\Services\Litecoin\Syncers\AddressSyncer;
use Denpa\Bitcoin\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PlayLTC extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'play-ltc';

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
        
        $blockCypher = new BlockCypher('ltc');
        $data = $blockCypher->getAddress('ltc1qlkyg42akhv440fp63nn27z42xxl9clxpqhz5uf', ['limit' => 2000, 'confirmations' => 1]);
        $txs = Arr::pluck($data['txrefs'], 'tx_hash');
        foreach ($txs as $txid) {
            $d = $this->client->getrawtransaction('6dd939443db84ec0c8fc738496dbf89e70056532cfbe98c34c9c48407e2b06eb', true)->result();
            dd($d);
        }
        dd(array_unique($txs));

    }

    private function sync($address)
    {
        $address = Blockchain\Litecoin\Address::firstOrCreate(['address' => $address]);

        // TODO: How to know whick syncer to use
        $service = new AddressSyncer($address);
        $service->syncInformation();
        $service->sync();
    }


}
