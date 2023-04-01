<?php

namespace App\Http\Controllers\Api\Blockchain;

use App\Events\DepthSync\Created;
use App\Http\Controllers\Controller;
use App\Http\Requests\Blockchain\DepthSync\ListRequest;
use App\Http\Resources\Blockchain\DepthSyncResource;
use \App\Models\Blockchain;
use App\Services\Sync\DepthSync\Creator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

    public function postCreate(Request $request)
    {
        $this->validate($request, [
            'address' => 'required',
            'blockchain' => 'required|in:LTC',
            'max_depth' => [
                'required',
                'integer',
                'min:1',
                'max:16'
            ],
            'direction' => [
                'required',
                Rule::in(Blockchain\DepthSync::getDirectionsList())
            ],
            'limit_addresses' => 'required|integer|min:1|max:50',
            'limit_transactions' => 'required|integer|min:1|max:128',
        ]);

        $address = $request->get('address');
        $blockchain = $request->get('blockchain');
        $depth = $request->get('max_depth');
        $direction = $request->get('direction');
        $limitAddresses = $request->get('limit_addresses');
        $limitTransactions = $request->get('limit_transactions');

        /** @var Blockchain\Litecoin\Address $address */
        $address = Blockchain\Litecoin\Address::firstOrCreate(['address' => $address]);
        if ($address->wasRecentlyCreated) {
            // TODO: Это ведь точно работа репозитория
            $address = $address->fresh();
        }

        $service = new Creator($address);
        $depthSync = $service->create($depth, $limitAddresses, $limitTransactions, $direction);

        event(new Created($depthSync));

        return new DepthSyncResource($depthSync);
    }

    public function postDelete(Blockchain\DepthSync $depthSync)
    {
        dd($depthSync);
    }
}
