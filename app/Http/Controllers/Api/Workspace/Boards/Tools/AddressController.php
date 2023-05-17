<?php

namespace App\Http\Controllers\Api\Workspace\Boards\Tools;

use App\Http\Controllers\Controller;
use App\Http\Resources\Blockchain\Litecoin\AddressResource;
use App\Jobs\Blockchain\Litecoin\SyncAddress;
use App\Repositories\Blockchain\Litecoin\AddressRepository;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function getDetails(AddressRepository $repository, Request $request)
    {
        $this->validate($request, ['address' => 'required', 'blockchain' => 'required|in:LTC']);

        $address = $request->get('address');
        $address = $repository->getAddressByAddress($address);

        if (!$address->isSynced2()) {
            dispatch(new SyncAddress($address->address));
        }

        return new AddressResource($address);
    }

    public function getNeighbors(AddressRepository $repository, Request $request)
    {
        $limit = min(100, $request->get('limit', 100));
        $ttl = 9999999;
        $this->validate($request, ['address' => 'required', 'blockchain' => 'required|in:LTC']);

        $address = $request->get('address');
        $address = $repository->getAddressByAddress($address);
        if (!$address->isSynced2($ttl)) {
            dispatch(new SyncAddress($address->address));

            abort(400, 'Address not synced. Try again later.');
        }

        $senders = $repository->toolGetSendersToAddress($address->address);
        $sendersAddresses = $repository->getAddresses(array_slice(\Arr::pluck($senders, 'address'), 0, $limit));
        $recipients = $repository->toolGetRecipientsByAddress($address->address);
        $recipientsAddresses = $repository->getAddresses(array_slice(\Arr::pluck($recipients, 'address'), 0, $limit));

        $response = [
            'senders' => AddressResource::collection($sendersAddresses),
            'recipients' => AddressResource::collection($recipientsAddresses),
            'senders_transactions_count' => \Arr::pluck($senders, 'tx_count', 'address'),
            'recipients_transactions_count' => \Arr::pluck($recipients, 'tx_count', 'address')
        ];

        return response()->json($response);
    }

    public function getDeepNeighbors()
    {
        // return Job
    }

}
