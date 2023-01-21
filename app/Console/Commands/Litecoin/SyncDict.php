<?php

namespace App\Console\Commands\Litecoin;

use Denpa\Bitcoin\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SyncDict extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ltc:sync-dict {startBlock}';

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
        $startBlock = (int)$this->argument('startBlock', null);
        if (empty($startBlock)) {
            $startBlock = DB::table('ltc_dict')->max('block_height');
        }

        $this->client = new Client(config('services.litecoin-wallet.host'));
        for ($height = $startBlock; $height < 2000000; $height++) {
            $t = microtime(true);
            $this->info('Handle block ' . $height);
            $rt = microtime(true);
            $blockHash = $this->client->getblockhash($height)->result();
            $data = $this->client->getblock($blockHash, 2)->result();
            $rt = microtime(true) - $rt;
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
                $rows = [];
                foreach ($addressesList as $address) {
                    $rows[] = [
                        'address' => $address,
                        'tx_hash' => $txid,
                        'block_height' => $height
                    ];
                }
                $inserts += DB::table('ltc_dict')
                    ->insertOrIgnore($rows);
            }

            $t = (microtime(true) - $t);
            $method = $t > 0.1 ? 'warn' : 'info';
            $this->$method('Transactions: ' . count($txs) . ' Inserts: ' . $inserts . ' Time: ' . $t . ' Request time: ' . $rt);
        }
    }
}
