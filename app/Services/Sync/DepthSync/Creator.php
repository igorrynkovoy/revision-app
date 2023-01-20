<?php

namespace App\Services\Sync\DepthSync;

use App\Jobs\Blockchain\Litecoin\SyncAddress;
use App\Models\Blockchain\Litecoin;
use App\Models\Blockchain\DepthSync;

class Creator
{
    protected $address;
    protected $parentDepthSync;

    public function __construct($address, DepthSync $parentDeptchSync = null)
    {
        $this->address = $address;
        $this->parentDepthSync = $parentDeptchSync;
    }

    public function create(int $depth, int $limitAddresses, int $limitTransactions, string $direction)
    {
        if (!in_array($direction, DepthSync::getDirectionsList())) {
            throw new \RuntimeException('Invalid direction type');
        }

        $depthSync = new DepthSync();
        $depthSync->address = $this->address->address;
        $depthSync->blockchain = Litecoin\Address::BLOCKCHAIN_SYMBOL;
        $depthSync->root_sync_id = null;
        $depthSync->limit_addresses = $limitAddresses;
        $depthSync->limit_transactions = $limitTransactions;
        $depthSync->max_depth = $depth;
        $depthSync->current_depth = 0;
        $depthSync->direction = $direction;
        $depthSync->save();

        dispatch(new SyncAddress($this->address->address));

        return $depthSync;
    }
}
