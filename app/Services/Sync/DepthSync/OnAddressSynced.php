<?php

namespace App\Services\Sync\DepthSync;

use App\Interfaces\Blockchain\Address\AddressEntity;
use App\Jobs\Sync\DepthSync\ProcessDepthSync;
use App\Models\Blockchain\DepthSync;
use App\Models\Blockchain\Litecoin\Address;
use Illuminate\Support\Facades\DB;

class OnAddressSynced
{
    private $address;

    public function __construct(AddressEntity $address)
    {
        $this->address = $address;
    }

    public function handle()
    {
        $depthSyncs = DepthSync::query()
            ->where('blockchain', Address::BLOCKCHAIN_SYMBOL)
            ->where('address', $this->address->address)
            ->where('status', DepthSync::STATUS_SYNCING)
            ->get();

        dump(sprintf('Mark address %s syncs (%s) as synced', $this->address->address, implode(',', $depthSyncs->pluck('id')->toArray())));

        foreach ($depthSyncs as $depthSync) {
            $this->markAsSynced($depthSync);

            /** @var DepthSync $depthSync */
            if ($depthSync->current_depth > 0) {
                // TODO: Load this count for all syncs in one query before the foreach cycle
                $notSyncedOnDepth = DepthSync::query()
                    ->where('root_sync_id', $depthSync->root_sync_id)
                    ->where('current_depth', $depthSync->current_depth)
                    ->where('status', DepthSync::STATUS_SYNCING)
                    ->count();

                if ($notSyncedOnDepth > 0) {
                    dump(sprintf('Not all addresses synced. Sync #%s, root sync #%s, depth %s', $depthSync->id, $depthSync->root_sync_id, $depthSync->current_depth));
                    continue;
                }
            }

            $idToProcess = $depthSync->root_sync_id ?? $depthSync->id;
            dump('Dispatch ProcessDepthSync ' . $idToProcess . ' on depth ' . $depthSync->current_depth);
            dispatch(new ProcessDepthSync($idToProcess, $depthSync->current_depth));
        }
    }

    private function markAsSynced(DepthSync $depthSync)
    {
        $depthSync->status = DepthSync::STATUS_SYNCED;
        $depthSync->status_code = 'on_address_sync';
        $depthSync->save();
    }

    public function markDepthSyncAddresses()
    {
        DepthSync::query()
            ->where('blockchain', Address::BLOCKCHAIN_SYMBOL)
            ->where('address', $this->address->address)
            ->where('address_synced', 0)
            ->update([
                'address_synced' => 1,
                'status' => DepthSync::STATUS_SYNCED,
                'status_code' => 'on_address_sync'
            ]);
    }

    public function continueDepthSyncs()
    {
        $list = DepthSync::query()
            ->getQuery()
            ->select(['current_depth', DB::raw('IF(root_sync_id IS NULL,id,root_sync_id) as sync_id')])
            ->where('blockchain', Address::BLOCKCHAIN_SYMBOL)
            ->where('address', $this->address->address)
            ->where('processed', 0)
            ->pluck('current_depth', 'sync_id');

        foreach ($list as $rootSyncId => $depth) {
            if ($depth > 0) {
                $notSyncedOnDepth = DepthSync::query()
                    ->where('root_sync_id', $rootSyncId)
                    ->where('current_depth', $depth)
                    ->where('address_synced', 0)
                    ->count();

                if ($notSyncedOnDepth > 0) {
                    dump('Not all addresses are synced');
                    // Not all addresses are synced
                    continue;
                }
            }

            dump('Dispatch ProcessDepthSync ' . $rootSyncId . ' on depth ' . $depth);
            dispatch(new ProcessDepthSync($rootSyncId, $depth));
        }
    }
}
