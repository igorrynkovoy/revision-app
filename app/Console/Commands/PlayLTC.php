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
        // MHyW5CdfjVcUxBTJFw1oVuvHVEGPNCXoKb
        $this->client = new Client(config('services.litecoin-wallet.host'));
        for ($height = 1300000; $height < 2000000; $height++) {
            $t = microtime(true);
            $this->info('Handle block ' . $height);
            $blockHash = $this->client->getblockhash($height)->result();
            $data = $this->client->getblock($blockHash, 2)->result();
            $txs = Arr::get($data, 'tx');
            foreach ($txs as $tx) {

                $txid = $tx['txid'];
                $vouts = Arr::get($tx, 'vout');
                $addressesList = [];
                foreach ($vouts as $vout) {
                    $addresses = isset($vout['scriptPubKey']) && isset($vout['scriptPubKey']['addresses']) ? $vout['scriptPubKey']['addresses'] : [];
                    $addressesList = array_merge($addressesList, $addresses);
                }

                $addressesList = array_unique($addressesList);
                $inserts = 0;
                foreach ($addressesList as $address) {
                    $inserts += DB::table('ltc_dict')
                        ->insertOrIgnore([
                            'address' => $address,
                            'tx_hash' => $txid,
                            'block_height' => $height
                        ]);
                }
            }

            $this->info('Transactions: ' . count($txs) . ' Inserts: ' . $inserts . ' Time: ' . (microtime(true) - $t));
        }
        return;
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
