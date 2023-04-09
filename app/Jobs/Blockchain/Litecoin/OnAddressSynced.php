<?php

namespace App\Jobs\Blockchain\Litecoin;

use App\Exceptions\Services\Sync\Blockchain\Litecoin\AddressSyncer\AddressNotFound;
use App\Jobs\Sync\DepthSync\ProcessDepthSync;
use App\Models\Blockchain\Litecoin\Address;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Sync\DepthSync;

class OnAddressSynced implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $addressValue;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $address)
    {
        $this->addressValue = $address;
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

        if (!$address) {
            throw new AddressNotFound('Address ' . $this->addressValue . ' not found.');
        }
        dump('Address synced ' . $address->address);
        $depthOnSync = new DepthSync\OnAddressSynced($address);
        $depthOnSync->handle();
    }
}
