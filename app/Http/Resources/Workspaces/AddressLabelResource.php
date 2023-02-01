<?php

namespace App\Http\Resources\Workspaces;

use App\Models\Workspace;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Workspace\AddressLabel
 */
class AddressLabelResource extends JsonResource
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
            'label' => $this->label,
            'description' => $this->description,
            'workspace_id' => $this->workspace_id,
            'blockchain' => $this->blockchain,
            'tag' => $this->tag
        ];
    }
}
