<?php

namespace App\Http\Resources\Blockchain;

use App\Http\Resources\Blockchain\Litecoin\AddressResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Blockchain\DepthSync
 */
class DepthSyncResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return  [
            'id' => $this->id,
            'blockchain' => $this->blockchain,
            'address_value' => $this->address,
            'address' => new AddressResource($this->addressModel), // TODO: Implement eager loading
            'parent_sync_id' => $this->parent_sync_id,
            'direction' => $this->direction,
            'child_addresses' => $this->child_addresses,
            'limit_addresses' => $this->limit_addresses,
            'limit_transactions' => $this->limit_transactions,
            'max_depth' => $this->max_depth,
            'current_depth' => $this->current_depth,
            'processed' => $this->processed,
            'processed_code' => $this->processed_code,
            'processed_at' => \Dates::toTimestamp($this->processed_at),
            'children' => DepthSyncResource::collection($this->whenLoaded('children'))
        ];
    }
}
