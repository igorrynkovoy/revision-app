<?php

namespace App\Events\Blockchain\Litecoin\Address;

use App\Http\Resources\Blockchain\Litecoin\AddressResource;
use App\Models\Blockchain\Litecoin\Address;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Updated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public $address;
    private string $plainAddress;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Address $address)
    {
        $this->plainAddress = $address->address;
        $this->address = (new AddressResource($address))->resolve();
    }

    public function broadcastAs()
    {
        return 'litecoin.address.updated';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('blockchain.litecoin.address.' . $this->plainAddress);
    }
}
