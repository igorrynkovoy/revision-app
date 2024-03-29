<?php

namespace App\Http\Resources\Blockchain\Litecoin;

use App\Models\Blockchain\Litecoin\Address;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Blockchain\Litecoin\Address
 */
class AddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'address' => $this->address,
            'blockchain' => Address::BLOCKCHAIN_SYMBOL,
            'synced' => [
                'block_number' => $this->synced_block_number,
                'first_block_number' => $this->synced_first_block_number,
                'last_block_number' => $this->synced_last_block_number,
                'transactions' => $this->synced_transactions,
                'last_sync_at' => \Dates::toTimestamp($this->last_sync_at),
                'last_transaction_hash' => $this->last_transaction_hash
            ],
            'blockchain_data' => [
                'transactions' => $this->blockchain_transactions,
                'first_tx_block' => $this->blockchain_first_tx_block,
                'last_tx_block' => $this->blockchain_last_tx_block,
                'data_updated_at' => \Dates::toTimestamp($this->blockchain_data_updated_at)
            ],
            'created_at' => \Dates::toTimestamp($this->created_at)
        ];
    }
}
