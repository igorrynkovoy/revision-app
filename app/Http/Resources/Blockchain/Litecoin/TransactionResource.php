<?php

namespace App\Http\Resources\Blockchain\Litecoin;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Blockchain\Litecoin\Transaction
 */
class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'hash' => $this->hash,
            'block_number' => $this->block_number,
            'is_coinbase' => $this->is_coinbase,
            'total_inputs' => $this->total_inputs,
            'inputs' => TransactionOutputResource::collection($this->whenLoaded('inputs')),
            'total_outputs' => $this->total_outputs,
            'outputs' => TransactionOutputResource::collection($this->whenLoaded('outputs')),
            'amount' => $this->amount,
            'processed' => $this->processed,
            'processed_at' => \Dates::toTimestamp($this->processed_at),
            'added_at' => \Dates::toTimestamp($this->added_at),
            'created_at' => \Dates::toTimestamp($this->created_at)
        ];
    }
}
