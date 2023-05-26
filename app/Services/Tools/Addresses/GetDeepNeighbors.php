<?php

namespace App\Services\Tools\Addresses;

use App\Models\Blockchain\DepthSync;
use App\Models\ToolResult\Address\DeepNeighbors;
use App\Models\Workspace\Board\Board;
use App\Models\Workspace\Board\BoardJob;
use App\Repositories\Blockchain\Litecoin\AddressRepository;
use App\Repositories\Interfaces\Blockchain\Litecoin\AddressRepositoryInterface;
use App\Services\Sync\DepthSync\Creator;

class GetDeepNeighbors
{
    public function createJob(Board $board, string $blockchain, string $address, int $depth): BoardJob
    {
        /** @var AddressRepository $addressRepository */
        $addressRepository = app(AddressRepositoryInterface::class);
        $address = $addressRepository->getAddressByAddress($address);
        $depthSyncCreator = new Creator($address);

        \DB::beginTransaction();

        $depthSync = $depthSyncCreator->create($depth, 20, 500, DepthSync::DIRECTION_BOTH);

        $tool = new DeepNeighbors();
        $tool->board_id = $board->id;
        $tool->depth_sync_id = $depthSync->id;
        $tool->settings = [
            'address' => $address,
            'blockchain' => $blockchain,
            'depth' => $depth
        ];
        $tool->save();

        $job = new BoardJob();
        $job->board_id = $board->id;
        $job->jobable()->associate($tool);
        $job->type = 'address_deep_neighbors';
        $job->save();

        $tool->job_id = $job->id;
        $tool->save();

        $job = $job->fresh();

        \DB::commit();

        $depthSyncCreator->runJobs($depthSync);

        return $job;
    }
}
