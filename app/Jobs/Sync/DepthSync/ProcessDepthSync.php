<?php

namespace App\Jobs\Sync\DepthSync;

use App\Models\Blockchain\DepthSync;
use App\Services\Sync\DepthSync\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessDepthSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $rootDepthSyncId;
    public int $depth;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $rootDepthSyncId, int $depth)
    {
        $this->rootDepthSyncId = $rootDepthSyncId;
        $this->depth = $depth;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /** @var DepthSync $rootSync */
        $rootSync = DepthSync::find($this->rootDepthSyncId);

        $service = new Service();
        $service->handleRootOnDepth($rootSync, $this->depth);
    }
}
