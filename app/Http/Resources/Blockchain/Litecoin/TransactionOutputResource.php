<?php

namespace App\Http\Resources\Blockchain\Litecoin;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Blockchain\Litecoin\TransactionOutput
 */
class TransactionOutputResource extends JsonResource
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
            'id' => $this->id,
            'address' => $this->address,
            'transaction_hash' => $this->transaction_hash,
            'index' => $this->index,
            'input_transaction_hash' => $this->input_transaction_hash,
            'input_index' => $this->input_index,
            'value' => $this->value,
            'script_type' => $this->script_type
        ];
    }
}
