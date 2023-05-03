<?php

namespace App\Http\Resources\Workspaces\Boards\Layouts;

use App\Models\Workspace;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Workspace\Board\BoardLayout
 */
class LayoutResource extends JsonResource
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
            'title' => $this->title,
            'layout' => $this->layout,
            'created_at' => \Dates::toTimestamp($this->created_at),
            'updated_at' => \Dates::toTimestamp($this->updated_at)
        ];
    }
}
