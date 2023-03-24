<?php

namespace App\Services\Litecoin\Syncers\ByAddress\Address;

use App\Models\Blockchain\Litecoin;
use App\Services\DataServices\Blockchain\BlockCypher;
use App\Services\DataServices\Blockchain\RemoteApp\RemoteAPI;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class Base
{
    protected $address;
    protected BlockCypher $blockCypher;
    protected RemoteAPI $remoteAPI;
    protected $lastSyncBlock;

    public function __construct(Litecoin\Address $address)
    {
        $this->address = $address;
        $this->blockCypher = new BlockCypher('ltc');
        $this->remoteAPI = new RemoteAPI(RemoteAPI::BLOCKCHAIN_LITECOIN);
    }

    protected function getTxs($address, $sinceBlock, $page, $limit): array
    {
        $list = $this->remoteAPI->getAddressTransactions($address, [
            'since_block' => $sinceBlock,
            'page' => $page,
            'limit' => $limit,
            'with_outputs' => 1
        ]);

        return $list;
    }

    protected function getList($address, $beforeBlock, $afterBlock, $limit): array
    {
        $t = microtime(true);
        dump('Get list', func_get_args());

        $list = $this->blockCypher->getAddressFull($address, [
            'before' => $beforeBlock,
            'after' => $afterBlock,
            'limit' => $limit,
            'txlimit' => 500
        ]);

        $result = Arr::get($list, 'txs', []);

        if (is_string($result)) {
            dump($result);
        }

        return $result;
    }
}
