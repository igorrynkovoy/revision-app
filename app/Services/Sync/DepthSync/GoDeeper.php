<?php

namespace App\Services\Sync\DepthSync;

use App\Exceptions\Services\Sync\DepthSync\InterruptException;
use App\Jobs\Blockchain\Litecoin\SyncAddress;
use App\Models\Blockchain;
use App\Models\Blockchain\DepthSync;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class GoDeeper
{
    public function goDeeper(DepthSync $parentDepthSync, Collection $oneHopAddresses)
    {
        if (!$parentDepthSync->addressModel->isSynced()) {
            throw new \RuntimeException('Address has not been synced yet');
        }

        if ($parentDepthSync->processed) {
            return;
        }

        if ($parentDepthSync->current_depth === $parentDepthSync->max_depth) {
            throw new InterruptException(sprintf('Address %s max depth reached.', $parentDepthSync->address), InterruptException::CODE_MAX_DEPTH);
        }

        if ($oneHopAddresses->count() > $parentDepthSync->limit_addresses) {
            throw new InterruptException(sprintf('Address %s has too many foreign addresses to sync.', $parentDepthSync->address), InterruptException::CODE_ADDRESS_LIMIT);
        }

        if ($parentDepthSync->addressModel->blockchain_transactions > $parentDepthSync->limit_transactions) {
            throw new InterruptException(sprintf('Address %s has too many transactions. Limit: %s. Actual: %s',
                $parentDepthSync->address, $parentDepthSync->limit_transactions, $parentDepthSync->addressModel->blockchain_transactions
            ), InterruptException::CODE_TRANSACTIONS_LIMIT);
        }

        foreach ($oneHopAddresses as $oneHopAddress) {
            /** @var Blockchain\Litecoin\Address $address */
            $address = Blockchain\Litecoin\Address::firstOrCreate(['address' => $oneHopAddress]);

            $depthSync = new DepthSync();
            $depthSync->address = $address->address;
            $depthSync->blockchain = Blockchain\Litecoin\Address::BLOCKCHAIN_SYMBOL;
            $depthSync->root_sync_id = $parentDepthSync->root_sync_id ?? $parentDepthSync->id;
            $depthSync->parent_sync_id = $parentDepthSync->id;
            $depthSync->direction = DepthSync::DIRECTION_BOTH;
            $depthSync->limit_addresses = $parentDepthSync->limit_addresses;
            $depthSync->limit_transactions = $parentDepthSync->limit_transactions;
            $depthSync->max_depth = $parentDepthSync->max_depth;
            $depthSync->current_depth = $parentDepthSync->current_depth + 1;
            $depthSync->save();

            $this->dispatchJobs($address->address);
        }
    }

    private function dispatchJobs($address)
    {
        dump('Dispatch ' . $address);
        SyncAddress::dispatch($address);
    }
}
