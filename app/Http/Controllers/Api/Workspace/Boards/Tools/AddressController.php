<?php

namespace App\Http\Controllers\Api\Workspace\Boards\Tools;

use App\Http\Controllers\Controller;
use App\Http\Resources\Blockchain\Litecoin\AddressResource;
use App\Http\Resources\Workspaces\Boards\Jobs\JobResource;
use App\Jobs\Blockchain\Litecoin\SyncAddress;
use App\Models\Workspace\Board\Board;
use App\Repositories\Blockchain\Litecoin\AddressRepository;
use App\Services\Tools\Addresses\GetDeepNeighbors;
use Graphp\Graph\Graph;
use Graphp\Graph\Vertex;
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
        $this->validate($request, ['address' => 'required', 'blockchain' => 'required|in:LTC']);

        $limit = min(100, $request->get('limit', 100));

        $ttl = 9999999;
        $address = $repository->getAddressByAddress($request->get('address'));
        if (!$address->isSynced2($ttl)) {
            dispatch(new SyncAddress($address->address));

            abort(400, 'Address not synced. Try again later.');
        }

        $senders = $repository->toolGetSendersToAddress($address->address);
        $senders = array_slice($senders, 0, $limit);
        $recipients = $repository->toolGetRecipientsByAddress($address->address);
        $recipients = array_slice($recipients, 0, $limit);


        $graph = new \App\Services\GraphResponse\Graph();
        $mainAddressNode = $graph->createNode($address->address)->setType('Address');
        foreach ($senders as $sender) {
            $senderNode = $graph->createNode($sender->address)->setType('Address');
            $graph->createEdge($senderNode, $mainAddressNode, null, ['tx_count' => $sender->tx_count])->setType('Send');
        }
        foreach ($recipients as $recipient) {
            $recipientNode = $graph->getNode($recipient->address) ?? $graph->createNode($recipient->address)->setType('Address');
            $graph->createEdge($mainAddressNode, $recipientNode, null, ['tx_count' => $recipient->tx_count])->setType('Send2');
        }

        $addresses = $repository->getAddresses(array_unique(array_merge(\Arr::pluck($senders, 'address'), \Arr::pluck($recipients, 'address'))));
        $response = [
            'addresses' => AddressResource::collection($addresses),
            'graph' => $graph->toArray()
        ];

        return response()->json(['data' => $response]);
    }

    public function getDeepNeighbors(Request $request)
    {
        $this->validate($request, ['address' => 'required', 'blockchain' => 'required|in:LTC', 'board_id' => 'required', 'depth' => 'required|integer|min:1|max:3']);

        $address = $request->get('address');
        $blockchain = $request->get('blockchain');
        $board = Board::find($request->get('board_id'));

        if (!$board) {
            abort(404, 'Board not found');
        }

        $depth = 3;

        $service = new GetDeepNeighbors();
        $boardJob = $service->createJob($board, $blockchain, $address, $depth);

        return new JobResource($boardJob);
    }

}
