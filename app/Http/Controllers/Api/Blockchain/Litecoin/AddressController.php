<?php

namespace App\Http\Controllers\Api\Blockchain\Litecoin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Blockchain\Litecoin\Addresses\ListRequest;
use App\Http\Resources\Blockchain\Litecoin\AddressResource;
use App\Models\Blockchain\Litecoin\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function getList(ListRequest $request)
    {
        $limit = min(100, $request->get('limit', 10));
        $order = $request->get('order', 'desc');
        $orderBy = $request->get('order_by', 'id');

        $addresses = Address::query()
            ->limit($limit)
            ->orderBy($orderBy, $order);

        if ($request->filled('address')) {
            $addresses->where('address', 'like', '%' . $request->get('address') . '%');
        }

        $addresses = $addresses->get();

        return AddressResource::collection($addresses);
    }
}
