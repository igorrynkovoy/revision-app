<?php

namespace App\Services\Sync\DepthSync\Graph;

use App\Models\Blockchain\DepthSync;

class Neo4JStore
{
    protected DepthSync $rootDepthSync;

    public function __construct(DepthSync $rootDepthSync)
    {
        $this->rootDepthSync = $rootDepthSync;
    }

    public function handle()
    {

    }

}
