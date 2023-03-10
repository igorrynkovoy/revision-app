<?php

namespace App\Services\Litecoin\Syncers\ByAddress;

use App\Exceptions\Services\Sync\Blockchain\Litecoin\AddressSyncer\AddressTransactionsLimit;
use App\Models\Blockchain\Litecoin;
use App\Models\Blockchain\Litecoin\Address;
use App\Services\DataServices\Blockchain\BlockCypher;
use App\Services\DataServices\Blockchain\RemoteApp\RemoteAPI;
use App\Services\Litecoin\Syncers\ByAddress\Address\Fresh;
use App\Services\Litecoin\Syncers\ByAddress\Address\Full;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AddressSyncer
{
    const SYNC_STEP_MODE_FRESH = 'fresh';
    const SYNC_STEP_MODE_FULL = 'full';

    protected Litecoin\Address $address;

    private BlockCypher $blockCypher;

    public function __construct(Litecoin\Address $address)
    {
        $this->address = $address;
        $this->blockCypher = new BlockCypher('ltc');
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

        $data = $this->remoteAPI->getAddressDetails($this->address->address);

        $this->address->blockchain_transactions = Arr::get($data, 'total_transactions');
        $this->address->blockchain_last_tx_block = Arr::get($data, 'last_block');
        $this->address->blockchain_first_tx_block = Arr::get($data, 'first_block');
        $this->address->blockchain_data_updated_at = Carbon::now();

        $this->address->save();
    }

    public function sync()
    {
        $this->updateSyncStatus($this->address, Address::SYNC_STATUS_SYNCING, 'started');

        if ($this->address->blockchain_last_tx_block === $this->address->synced_block_number) {
            // TODO: Мы начинаем с нового блока, нет гарантий в эой проверке
            //return;
        }

        if ($this->address->blockchain_transactions > 30000) {
            //throw new AddressTransactionsLimit('Address ' . $this->address->address . ' has too many transactions. Total: ' . $this->address->blockchain_transactions);
        }

        $syncMode = empty($this->address->blockchain_first_tx_block) || $this->address->synced_first_block_number !== $this->address->blockchain_first_tx_block ? self::SYNC_STEP_MODE_FULL : self::SYNC_STEP_MODE_FRESH;

        if ($syncMode === self::SYNC_STEP_MODE_FULL) {
            $this->updateSyncStatus($this->address, Address::SYNC_STATUS_SYNCING, 'full_sync');

            $syncer = new Full($this->address);
            $syncer->sync();

            $this->updateSyncStatus($this->address, Address::SYNC_STATUS_SUCCESS, 'full_sync');
        } else {
            $this->updateSyncStatus($this->address, Address::SYNC_STATUS_SYNCING, 'fresh_sync');

            $syncer = new Fresh($this->address);
            $syncer->sync();

            $this->updateSyncStatus($this->address, Address::SYNC_STATUS_SUCCESS, 'fresh_sync');
        }
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
