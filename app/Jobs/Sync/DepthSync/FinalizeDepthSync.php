<?php

namespace App\Jobs\Sync\DepthSync;

use App\Models\Blockchain\DepthSync;
use App\Services\Sync\DepthSync\Finalize;
use App\Services\Sync\DepthSync\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FinalizeDepthSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $rootDepthSyncId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $rootDepthSyncId)
    {
        $this->rootDepthSyncId = $rootDepthSyncId;
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

        if (!$rootSync) {
            \Log::debug(sprintf('%s job canceled. Depth sync with ID#%s not found.', static::class, $this->rootDepthSyncId));
            return;
        }

        if($rootSync->stop_sync) {
            \Log::debug(sprintf('%s job canceled. Depth sync #%s stop_sync = true.', static::class, $this->rootDepthSyncId));
            return;
        }

        $service = new Finalize($rootSync);
        $service->handle();
    }
}
