<?php

namespace App\Http\Controllers\Api\Blockchain\Litecoin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Blockchain\Litecoin\AddressResource;
use App\Models\Blockchain\Litecoin\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function getList(Request $request)
    {
        $limit = min(30, $request->get('limit', 10));
        $order = $request->get('order', 'desc');
        $sortBy = $request->get('sort_by', 'id');
        $sortBy = in_array($sortBy, ['id', 'address', 'last_sync_at', 'blockchain_transactions', 'blockchain_last_tx_block']) ? $sortBy : 'id';

        $addresses = Address::query()
            ->limit($limit)
            ->orderBy($sortBy, $order);

        if ($request->filled('address')) {
            $addresses->where('address', $request->get('address'));
        }

        $addresses = $addresses->get();

        return AddressResource::collection($addresses);
    }
}
