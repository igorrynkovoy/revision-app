<?php

namespace App\Http\Controllers\Api\Blockchain\Litecoin\Explorer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Blockchain\Litecoin\TransactionResource;
use App\Models\Blockchain\Litecoin\Transaction;
use Illuminate\Http\Request;

class TransactionsController extends Controller
{
    public function getByAddress(string $address, Request $request)
    {
        $limit = $request->get('limit', 50);
        $page = $request->get('page', 1);
        $order = $request->get('order', 'asc');

        $transactions = Transaction::query()
            ->leftJoin('litecoin_transactions_addresses', 'litecoin_transactions.hash', '=', 'litecoin_transactions_addresses.transaction_hash')
            ->where('litecoin_transactions_addresses.address', $address)
            ->orderBy('litecoin_transactions_addresses.block_number', $order)
            ->orderBy('litecoin_transactions_addresses.transaction_hash')
            ->forPage($page, $limit);

        if($request->boolean('with_outputs')) {
            $transactions->with(['inputs', 'outputs']);
        }

        if ($request->filled('before_block')) {
            $transactions->where('litecoin_transactions_addresses.block_number', '<', $request->get('before_block'));
        }

        if ($request->filled('since_block')) {
            $transactions->where('litecoin_transactions_addresses.block_number', '>=', $request->get('since_block'));
        }

        $transactions = $transactions->get();

        return TransactionResource::collection($transactions);
    }
}
