<?php

namespace App\Http\Controllers\Api\Blockchain;

use App\Events\DepthSync\Created;
use App\Http\Controllers\Controller;
use App\Http\Requests\Blockchain\DepthSync\CreateRequest;
use App\Http\Requests\Blockchain\DepthSync\ListRequest;
use App\Http\Resources\Blockchain\DepthSyncResource;
use \App\Models\Blockchain;
use App\Repositories\Blockchain\Litecoin\AddressRepository;
use App\Services\Sync\DepthSync\Creator;
use App\Services\Sync\DepthSync\Delete;

class DepthSyncController extends Controller
{
    public function getList(ListRequest $request)
    {
        $limit = min(100, $request->get('limit', 100));
        $page = max(1, $request->get('page', 1));

        $order = $request->get('order', 'desc');
        $orderBy = $request->get('order_by', 'id');
        $orderBy = in_array($orderBy, ['id', 'address', 'child_addresses', 'current_depth', 'processed', 'processed_at']) ? $orderBy : 'id';

        $list = Blockchain\DepthSync::query()
            ->whereNull('root_sync_id')
            ->orderBy($orderBy, $order)
            ->forPage($page, $limit);

        if ($request->filled('address')) {
            $list->where('address', $request->get('address'));
        }

        if ($request->filled('processed')) {
            $list->where('processed', $request->boolean('processed'));
        }

        if ($request->filled('direction')) {
            $list->where('direction', (string)$request->get('direction'));
        }

        $list = $list->get();

        return DepthSyncResource::collection($list);
    }

    public function getDepthSync(Blockchain\DepthSync $depthSync, ?int $depth = null)
    {
        $depthSync->load(['children' => function ($query) {
            $query->orderBy('current_depth', 'asc');
            return $query;
        }]);
        return new DepthSyncResource($depthSync);
    }

    public function postCreate(AddressRepository $repository, CreateRequest $request)
    {
        $address = $request->get('address');
        $blockchain = $request->get('blockchain');

        $address = $repository->getAddressByAddress($address);

        $service = new Creator($address);
        $service->setLimitAddresses($request->get('limit_addresses'))
            ->setLimitTransactions($request->get('limit_transactions'))
            ->setMaxDepth($request->get('max_depth'));

        $depthSync = $service->create($request->get('direction'));
        $service->runJobs($depthSync);

        event(new Created($depthSync));

        return new DepthSyncResource($depthSync);
    }

    public function postDelete(Blockchain\DepthSync $depthSync)
    {
        $service = new Delete($depthSync);
        $service->deleteAll();

        return response()->json(['success' => true]);
    }
}
