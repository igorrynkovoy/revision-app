<?php

namespace App\Services\Sync\DepthSync;

use App\Events\DepthSync\Created;
use App\Exceptions\Services\Sync\DepthSync\InterruptException;
use App\Models\Blockchain;
use App\Models\Blockchain\DepthSync;
use App\Services\Litecoin\AddressSummary;
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

            $creator = new Creator($address, $parentDepthSync);
            $depthSync = $creator->create();
            $creator->runJobs($depthSync);
            event(new Created($depthSync));
        }
    }
}
