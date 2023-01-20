<?php

namespace App\Http\Controllers\Api\Blockchain;

use App\Http\Controllers\Controller;
use App\Http\Resources\Blockchain\DepthSyncResource;
use \App\Models\Blockchain;
use App\Services\Sync\DepthSync\Creator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepthSyncController extends Controller
{
    public function getList(Request $request)
    {
        $limit = min(30, $request->get('limit', 10));
        $order = $request->get('order', 'desc');
        $sortBy = $request->get('sort_by', 'id');
        $sortBy = in_array($sortBy, ['id', 'address', 'child_addresses', 'current_depth', 'processed', 'processed_at']) ? $sortBy : 'id';

        $list = Blockchain\DepthSync::query()
            ->whereNull('root_sync_id')
            ->orderBy($sortBy, $order)
            ->limit($limit);

        if ($request->filled('address')) {
            $list->where('address', $request->get('address'));
        }

        $list = $list->get();

        return DepthSyncResource::collection($list);
    }

    public function getDepthSync(Blockchain\DepthSync $depthSync, ?int $depth = null)
    {
        // TODO: Add children
        return new DepthSyncResource($depthSync);
    }

    public function postCreate(Request $request)
    {
        $this->validate($request, [
            'address' => 'required',
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
            'limit_address' => 'required|integer|min:1|max:32',
            'limit_transactions' => 'required|integer|min:1|max:128',
        ]);

        $address = $request->get('address');
        $depth = $request->get('max_depth');
        $direction = $request->get('direction');
        $limitAddresses = $request->get('limit_address');
        $limitTransactions = $request->get('limit_transactions');

        /** @var Blockchain\Litecoin\Address $address */
        $address = Blockchain\Litecoin\Address::firstOrCreate(['address' => $address]);

        $service = new Creator($address);
        $depthSync = $service->create($depth, $limitAddresses, $limitTransactions, $direction);

        return new DepthSyncResource($depthSync);
    }

    public function postDelete(Blockchain\DepthSync $depthSync)
    {
        dd($depthSync);
    }
}
