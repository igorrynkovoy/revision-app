<?php

namespace App\Services\Sync\DepthSync;

use App\Exceptions\Services\Sync\DepthSync\InterruptException;
use App\Jobs\Blockchain\Litecoin\SyncAddress;
use App\Models\Blockchain;
use App\Models\Blockchain\DepthSync;
use App\Services\Litecoin\AddressSummary;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class GoDeeper
{
    public function goDeeper(DepthSync $parentDepthSync, Collection $oneHopAddresses)
    {
        if (!$parentDepthSync->addressModel->isSynced()) {
            throw new \RuntimeException('Address has not been synced yet');
        }

        if ($parentDepthSync->status !== DepthSync::STATUS_SYNCED) {
            throw new \RuntimeException(sprintf('Depthsync #%s has invalid status %s', $parentDepthSync->id, $parentDepthSync->status));
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

            try {
                $service = new AddressSummary($address);
                $address = $service->handle();
            } catch (\Exception $e) {
                \Log::error(sprintf('Unable to get address %s summary. Error: %s.', $address->address, $e->getMessage()));
            }

            $depthSync = new DepthSync();
            $depthSync->address = $address->address;
            $depthSync->blockchain = Blockchain\Litecoin\Address::BLOCKCHAIN_SYMBOL;
            $depthSync->root_sync_id = $parentDepthSync->root_sync_id ?? $parentDepthSync->id;
            $depthSync->parent_sync_id = $parentDepthSync->id;
            $depthSync->direction = DepthSync::DIRECTION_BOTH;
            $depthSync->status = DepthSync::STATUS_PENDING;
            $depthSync->status_code = 'created_on_deeper';
            $depthSync->limit_addresses = $parentDepthSync->limit_addresses;
            $depthSync->limit_transactions = $parentDepthSync->limit_transactions;
            $depthSync->max_depth = $parentDepthSync->max_depth;
            $depthSync->current_depth = $parentDepthSync->current_depth + 1;
            if ($address->blockchain_transactions > $parentDepthSync->limit_transactions) {
                $depthSync->status = DepthSync::STATUS_INTERRUPTED;
                $depthSync->status_code = 'transactions_limit';
                $depthSync->processed = true;
                $depthSync->processed_at = now();
                $depthSync->address_synced = true;
                $depthSync->processed_code = InterruptException::CODE_TRANSACTIONS_LIMIT;
            }

            $depthSync->save();

            if ($depthSync->status === DepthSync::STATUS_PENDING) {
                $this->dispatchJobs($depthSync);
            }
        }
    }

    private function dispatchJobs(DepthSync $depthSync)
    {
        dump('Dispatch ' . $depthSync->address);
        SyncAddress::dispatch($depthSync->address);

        $depthSync->status = DepthSync::STATUS_SYNCING;
        $depthSync->status_code = 'sync_jobs_created';
        $depthSync->save();
    }
}
