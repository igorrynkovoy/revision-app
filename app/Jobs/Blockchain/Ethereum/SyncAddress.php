<?php

namespace App\Jobs\Blockchain\Ethereum;

use App\Models\Blockchain\Ethereum\Address;
use App\Services\Ethereum\Syncers\AddressSyncer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncAddress implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $backoff = 3;

    public string $addressValue;
    public ?string $forceSyncMethod;

    protected $etherScan;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $addressValue, string $forceSyncMethod = null)
    {
        $this->addressValue = $addressValue;
        $this->forceSyncMethod = $forceSyncMethod;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /** @var Address $address */
        $address = Address::query()
            ->firstWhere('address', $this->addressValue);

        $syncer = new AddressSyncer($address);
        $syncer->syncInformation();
        $syncer->sync();
    }
}
