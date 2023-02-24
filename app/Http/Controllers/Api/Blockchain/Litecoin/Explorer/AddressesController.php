<?php

namespace App\Http\Controllers\Api\Blockchain\Litecoin\Explorer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AddressesController extends Controller
{
    public function getDetails(string $address, Request $request)
    {
        $stats = \DB::table('litecoin_transactions_addresses')
            ->select([\DB::raw('COUNT(*) as total'), \DB::raw('MIN(block_number) as first_block'), \DB::raw('MAX(block_number) as last_block')])
            ->where('address', $address)
            ->first();

        $data = [
            'address' => $address,
            'neighbors' => 0, // TODO
            'total_transactions' => $stats->total,
            'first_block' => $stats->first_block,
            'last_block' => $stats->last_block
        ];

        return response()->json($data);
    }
}
