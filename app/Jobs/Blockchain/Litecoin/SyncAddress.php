<?php

namespace App\Jobs\Blockchain\Litecoin;

use App\Exceptions\Services\Sync\Blockchain\Litecoin\AddressSyncer\AddressNotFound;
use App\Exceptions\Services\Sync\Blockchain\Litecoin\AddressSyncer\AddressTransactionsLimit;
use App\Models\Blockchain\Litecoin\Address;
use App\Services\Litecoin\Syncers\AddressSyncer;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncAddress implements ShouldQueue//, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $backoff = 2;
    public $tries = 2;

    public string $addressValue;
    public ?string $forceSyncMethod;

    protected $etherScan;
    protected AddressSyncer $addressSyncer;

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
        dump(sprintf('Litecoin/SyncAddress started for %s with %s attempt', $this->addressValue, $this->attempts()));

        /** @var Address $address */
        $address = Address::query()
            ->firstWhere('address', $this->addressValue);

        if (!$address) {
            throw new AddressNotFound('Address ' . $this->addressValue . ' not found.');
        }

        $this->addressSyncer = new AddressSyncer($address);

        try {
            $this->addressSyncer->syncInformation();
            $this->addressSyncer->sync();
        } catch (\Exception $e) {
            if ($e instanceof AddressTransactionsLimit) {
                $this->addressSyncer->updateSyncStatus($address->address, Address::SYNC_STATUS_FAILED, 'transactions_limit');
            } elseif ($e instanceof RequestException) {
                $this->addressSyncer->updateSyncStatus($address->address, Address::SYNC_STATUS_FAILED, 'api_failed');
            }

            throw $e;
        }


        dump('Litecoin/SyncAddress finished for ' . $this->addressValue);

        dispatch(new OnAddressSynced($address->address, Address::BLOCKCHAIN_NAME));
    }

    public function failed(\Throwable $exception)
    {
        dump('Failed with ' . $exception->getMessage());
    }

    public function __uniqueId()
    {
        return 'LTC-' . $this->addressValue;
    }
}
