<?php

namespace App\Services\Sync\DepthSync;

use App\Exceptions\Services\Sync\DepthSync\InterruptException;
use App\Interfaces\Blockchain\Address\AddressEntity;
use App\Jobs\Blockchain\Litecoin\SyncAddress;
use App\Jobs\Sync\DepthSync\ProcessDepthSync;
use App\Models\Blockchain\Litecoin;
use App\Models\Blockchain\DepthSync;
use App\Services\Litecoin\BlockchainLitecoin;

class Creator
{
    public const DEFAULT_LIMIT_ADDRESSES = 10;
    public const DEFAULT_LIMIT_TRANSCTIONS = 100;
    public const DEFAULT_MAX_DEPTH = 3;

    protected AddressEntity $address;
    protected ?DepthSync $parentDepthSync;
    protected BlockchainLitecoin $blockchain;

    private int $limitAddresses;
    private int $limitTransactions;
    private int $maxDepth;

    public function __construct($address, DepthSync $parentDeptchSync = null)
    {
        $this->address = $address;
        $this->parentDepthSync = $parentDeptchSync;
        $this->blockchain = new BlockchainLitecoin();

        $this->limitAddresses = $this->parentDepthSync?->limit_addresses ?? self::DEFAULT_LIMIT_ADDRESSES;
        $this->limitTransactions = $this->parentDepthSync?->limit_transactions ?? self::DEFAULT_LIMIT_TRANSCTIONS;
        $this->maxDepth = $this->parentDepthSync?->max_depth ?? self::DEFAULT_MAX_DEPTH;
    }

    public function setLimitAddresses(int $value)
    {
        $this->limitAddresses = $value;

        return $this;
    }

    public function setLimitTransactions(int $value)
    {
        $this->limitTransactions = $value;

        return $this;
    }

    public function setMaxDepth(int $value)
    {
        $this->maxDepth = $value;

        return $this;
    }

    public function create(string $direction = DepthSync::DIRECTION_BOTH): DepthSync
    {
        if (!in_array($direction, DepthSync::getDirectionsList())) {
            throw new \RuntimeException('Invalid direction type');
        }

        $depthSync = new DepthSync();
        $depthSync->address = $this->address->address;
        $depthSync->blockchain = Litecoin\Address::BLOCKCHAIN_SYMBOL;
        $depthSync->root_sync_id = $this->getRootSyncId();
        $depthSync->parent_sync_id = $this->parentDepthSync?->id;
        $depthSync->direction = $direction;
        $depthSync->status = DepthSync::STATUS_PENDING;
        $depthSync->status_code = 'created';
        $depthSync->limit_addresses = $this->limitAddresses;
        $depthSync->limit_transactions = $this->limitTransactions;
        $depthSync->max_depth = $this->maxDepth;
        $depthSync->current_depth = $this->getCurrentDepth();
        $depthSync->address_synced = $this->isAddressSynced();

        if ($this->parentDepthSync && $this->address->blockchain_transactions > $this->parentDepthSync->limit_transactions) {
            $depthSync = $this->makeInterrupted($depthSync);
        }

        $depthSync->save();

        return $depthSync;
    }

    public function runJobs(DepthSync $depthSync): DepthSync
    {
        if ($depthSync->status !== DepthSync::STATUS_PENDING) {
            return $depthSync;
        }

        if ($depthSync->address_synced) {
            dispatch(new ProcessDepthSync($depthSync->id, $depthSync->current_depth));

            $depthSync->status = DepthSync::STATUS_SYNCED;
            $depthSync->status_code = 'synced';
            $depthSync->save();
        } else {
            dispatch(new SyncAddress($this->address->address));

            $depthSync->status = DepthSync::STATUS_SYNCING;
            $depthSync->status_code = 'sync_jobs_created';
            $depthSync->save();
        }

        return $depthSync;
    }

    private function isAddressSynced()
    {
        return $this->address->isSynced2();
    }

    private function getCurrentDepth(): int
    {
        return $this->parentDepthSync instanceof DepthSync ? $this->parentDepthSync->current_depth + 1 : 0;
    }

    private function getRootSyncId()
    {
        if (isset($this->parentDepthSync)) {
            return $this->parentDepthSync->root_sync_id ?? $this->parentDepthSync->id;
        }

        return null;
    }

    private function makeInterrupted(DepthSync $depthSync)
    {
        $depthSync->status = DepthSync::STATUS_INTERRUPTED;
        $depthSync->status_code = 'transactions_limit';
        $depthSync->processed = true;
        $depthSync->processed_at = now();
        $depthSync->address_synced = true;
        $depthSync->processed_code = InterruptException::CODE_TRANSACTIONS_LIMIT;

        return $depthSync;
    }
}
