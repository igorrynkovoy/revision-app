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

    public function markDepthSyncAddresses()
    {
        DepthSync::query()
            ->where('blockchain', Address::BLOCKCHAIN_SYMBOL)
            ->where('address', $this->address->address)
            ->where('address_synced', 0)
            ->update([
                'address_synced' => 1
            ]);
    }

    public function continueDepthSyncs()
    {
        $list = DepthSync::query()
            ->getQuery()
            ->select(['current_depth', DB::raw('IF(root_sync_id IS NULL,id,root_sync_id) as sync_id')])
            ->where('address', $this->address->address)
            ->where('processed', 0)
            ->pluck('current_depth', 'sync_id');

        foreach ($list as $rootSyncId => $depth) {
            if($depth > 0) {
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
