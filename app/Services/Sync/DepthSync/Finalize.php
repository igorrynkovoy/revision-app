<?php

namespace App\Services\Sync\DepthSync;

use App\Models\Blockchain\DepthSync;
use App\Models\ToolResult\Address\DeepNeighbors;

class Finalize
{
    private DepthSync $depthSync;

    public function __construct(DepthSync $depthSync)
    {
        $this->depthSync = $depthSync;
    }

    public function handle()
    {
        $this->finishBoardJobs();
    }

    private function finishBoardJobs()
    {
        $toolDeepNeighbors = DeepNeighbors::where('depth_sync_id', $this->depthSync->id)->first();

        if ($toolDeepNeighbors) {
            //dispatch()
        }
    }
}
