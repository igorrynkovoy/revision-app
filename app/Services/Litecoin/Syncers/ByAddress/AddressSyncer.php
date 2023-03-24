<?php

namespace App\Services\Litecoin\Syncers\ByAddress;

use App\Models\Blockchain\Litecoin;
use App\Models\Blockchain\Litecoin\Address;
use App\Services\DataServices\Blockchain\RemoteApp\RemoteAPI;
use App\Services\Litecoin\AddressSummary;
use App\Services\Litecoin\Syncers\ByAddress\Address\Full;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class AddressSyncer
{
    protected Litecoin\Address $address;

    public function __construct(Litecoin\Address $address)
    {
        $this->address = $address;
        $this->remoteAPI = new RemoteAPI(RemoteAPI::BLOCKCHAIN_LITECOIN);
    }

    public function getAddress(): Litecoin\Address
    {
        return $this->address;
    }

    public function syncInformation()
    {
        dump('Sync information about ' . $this->address->address);
        $this->address->sync_status = 'syncing_blockchain_data';
        $this->address->sync_status_code = 'ok';
        $this->address->save();

        $summary = new AddressSummary($this->address);
        $summary->handle();
    }

    public function sync()
    {
        $this->updateSyncStatus($this->address, Address::SYNC_STATUS_SYNCING, 'started');

        if ($this->address->blockchain_transactions > 30000) {
            //throw new AddressTransactionsLimit('Address ' . $this->address->address . ' has too many transactions. Total: ' . $this->address->blockchain_transactions);
        }

        $this->updateSyncStatus($this->address, Address::SYNC_STATUS_SYNCING, 'remote_sync');

        $syncer = new Full($this->address);
        $syncer->sync();

        $this->updateSyncStatus($this->address, Address::SYNC_STATUS_SUCCESS, 'remote_sync');
    }

    public function updateSyncStatus(string|Litecoin\Address $address, $status, $statusCode)
    {
        if ($address instanceof Litecoin\Address) {
            $address->sync_status = $status;
            $address->sync_status_code = $statusCode;
            $address->save();
        } else {
            Address::query()
                ->where('address', $address)
                ->update([
                    'sync_status' => 'failed',
                    'sync_status_code' => 'unhandled_error'
                ]);
        }
    }
}
