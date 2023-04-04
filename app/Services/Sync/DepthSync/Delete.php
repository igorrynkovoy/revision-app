<?php

namespace App\Services\Sync\DepthSync;

use App\Events\DepthSync\Deleted;
use App\Models\Blockchain\DepthSync;

class Delete
{
    private DepthSync $depthSync;

    public function __construct(DepthSync $depthSync)
    {
        $this->depthSync = $depthSync;
    }

    public function deleteAll(): void
    {
        \DB::beginTransaction();

        $this->depthSync->children()->delete();
        $this->depthSync->delete();

        \DB::commit();

        event(new Deleted($this->depthSync->id));
    }

}
