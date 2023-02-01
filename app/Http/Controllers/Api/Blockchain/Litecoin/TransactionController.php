<?php

namespace App\Http\Controllers\Api\Blockchain\Litecoin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Blockchain\Litecoin\AddressResource;
use App\Http\Resources\Blockchain\Litecoin\TransactionResource;
use App\Models\Blockchain\Litecoin\Address;
use App\Models\Blockchain\Litecoin\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function getList(Request $request)
    {
        $limit = min(100, $request->get('limit', 100));
        $page = max(1, $request->get('page', 1));

        $order = $request->get('order', 'desc');
        $sortBy = $request->get('sort_by', 'id');
        $sortBy = in_array($sortBy, ['block_number', 'hash']) ? $sortBy : 'block_number';

        $transactions = Transaction::query()
            ->forPage($page, $limit)
            ->orderBy($sortBy, $order);

        if ($request->filled('block_number')) {
            $transactions->where('block_number', $request->get('block_number'));
        }

        if ($request->filled('hash')) {
            $transactions->where('hash', $request->get('hash'));
        }

        if ($request->filled('address')) {
            $transactions->select(['litecoin_transactions.*'])
                ->leftJoin('litecoin_transactions_addresses', 'litecoin_transactions.hash', '=', 'litecoin_transactions_addresses.transaction_hash')
                ->where('litecoin_transactions_addresses.address', $request->get('address'));
        }

        $transactions = $transactions->get();

        return TransactionResource::collection($transactions);
    }

    public function getTransaction(string $txhash)
    {
        $transaction = Transaction::query()
            ->where('hash', $txhash)
            ->with(['inputs', 'outputs'])
            ->firstOrFail();

        $resource = new  TransactionResource($transaction);

        return $resource;
    }
}
