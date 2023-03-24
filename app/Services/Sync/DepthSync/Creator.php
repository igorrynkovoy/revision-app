<?php

namespace App\Services\Sync\DepthSync;

use App\Interfaces\Blockchain\Address\AddressEntity;
use App\Jobs\Blockchain\Litecoin\SyncAddress;
use App\Jobs\Sync\DepthSync\ProcessDepthSync;
use App\Models\Blockchain\Litecoin;
use App\Models\Blockchain\DepthSync;
use App\Services\Litecoin\BlockchainLitecoin;

class Creator
{
    protected AddressEntity $address;
    protected $parentDepthSync;
    protected BlockchainLitecoin $blockchain;

    public function __construct($address, DepthSync $parentDeptchSync = null)
    {
        $this->address = $address;
        $this->parentDepthSync = $parentDeptchSync;
        $this->blockchain = new BlockchainLitecoin();
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
        $depthSync->address_synced = $this->isAddressSynced();
        $depthSync->save();

        $this->runJobs($depthSync);

        return $depthSync;
    }

    private function isAddressSynced()
    {
        return $this->address->isSynced2();
    }

    private function runJobs(DepthSync $depthSync)
    {
        if ($depthSync->address_synced) {
            dispatch(new ProcessDepthSync($depthSync->id, $depthSync->current_depth));
        } else {
            dispatch(new SyncAddress($this->address->address));
        }
    }
}
