<?php

namespace App\Services\Sync\DepthSync;

use App\Models\Blockchain\DepthSync;

class Finalize
{
    private DepthSync $depthSync;

    public function __construct(DepthSync $depthSync)
    {
        $this->depthSync = $depthSync;
    }

    public function handle()
    {

    }

    private function finishBoardJobs()
    {

    }
}
