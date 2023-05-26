<?php

namespace App\Http\Resources\Workspaces\Boards\Jobs;

use App\Models\Workspace;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Workspace\Board\BoardJob
 */
class JobResource extends JsonResource
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
            'board_id' => $this->board_id,
            'jobable_type' => $this->jobable_type,
            'jobable_id' => $this->jobable_id,
            'type' => $this->type,
            'finished' => (bool)$this->finished,
            'finished_at' => \Dates::toTimestamp($this->finished_at),
            'created_at' => \Dates::toTimestamp($this->created_at),
            'updated_at' => \Dates::toTimestamp($this->updated_at)
        ];
    }
}
