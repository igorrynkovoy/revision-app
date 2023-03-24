<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BootstrapResource extends JsonResource
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
            'socket_domain' => config('services.socket.domain'),
            'server_time' => time()
        ];
    }
}
