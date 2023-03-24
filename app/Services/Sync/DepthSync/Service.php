<?php

namespace App\Services\Sync\DepthSync;

use App\Events\DepthSync\Updated;
use App\Exceptions\Services\Sync\DepthSync\InterruptException;
use App\Interfaces\Blockchain\Address\AddressEntity;
use App\Models\Blockchain\DepthSync;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class Service
{
    public function create($address, int $depth, int $limitAddresses, int $limitTransactions): DepthSync
    {
        $creator = new Creator($address);
        return $creator->create($depth, $limitAddresses, $limitTransactions, DepthSync::DIRECTION_BOTH);
    }

    public function handleRootOnDepth(DepthSync $rootSync, int $depth)
    {
        if (!$rootSync->isRoot()) {
            throw new \RuntimeException('Depth Sync ' . $rootSync->id . ' is not a root sync');
        }

        if ($depth === 0) {
            $this->handle($rootSync);
            return;
        }

        $children = $rootSync
            ->children()
            ->where('current_depth', $depth)
            ->get();

        foreach ($children as $depthSync) {
            $this->handle($depthSync);
        }
    }

    private function handle(DepthSync $depthSync)
    {
        // TODO: If all are synced, maybe rerun job to go deeper?
        if ($depthSync->processed) {
            dump(sprintf('Depth sync %s has been already processed', $depthSync->id));
            return;
        }

        dump('Go deeper for ' . $depthSync->address . ' in direction ' . $depthSync->direction);

        $goDeeper = new GoDeeper();
        $listService = new AddressList();

        $addressesList = match ($depthSync->direction) {
            DepthSync::DIRECTION_BOTH => $listService->getOneHopAddresses($depthSync),
            DepthSync::DIRECTION_RECIPIENT => $listService->getRecipientsAddresses($depthSync),
            DepthSync::DIRECTION_SENDER => $listService->getSendersAddresses($depthSync),
        };

        try {
            $goDeeper->goDeeper($depthSync, $addressesList);
        } catch (InterruptException $exception) {
            $interruptCode = $exception->getInterruptCode();
            dump($exception->getMessage());
        }

        $depthSync->processed_at = Carbon::now();
        $depthSync->processed = true;
        $depthSync->processed_code = $interruptCode ?? 'processed';
        $depthSync->child_addresses = $addressesList->count();
        $depthSync->save();

        event(new Updated($depthSync));
    }
}
