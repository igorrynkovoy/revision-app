<?php

namespace App\Services\Litecoin\Syncers\ByAddress;

use App\Events\Blockchain\Litecoin\Address\Updated;
use App\Models\Blockchain\Litecoin;
use App\Models\Blockchain\Litecoin\Address;
use App\Services\DataServices\Blockchain\RemoteApp\RemoteAPI;
use App\Services\Litecoin\AddressSummary;
use App\Services\Litecoin\BlockchainLitecoin;
use App\Services\Litecoin\Syncers\ByAddress\Address\RemoteFullSyncer;
use Illuminate\Support\Facades\DB;

class AddressSyncer
{
    protected Litecoin\Address $address;

    public function __construct(Litecoin\Address $address)
    {
        $this->address = $address;
        $this->remoteAPI = new RemoteAPI(RemoteAPI::BLOCKCHAIN_LITECOIN);
        $this->blockchain = new BlockchainLitecoin();
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

        event(new Updated($this->address));
    }

    public function sync()
    {
        if ($this->blockchain->getSyncMode() === BlockchainLitecoin::SYNC_MODE_FULL) {
            $this->fullSyncCase();
        } else {
            $this->remoteSyncCase();
        }

        event(new Updated($this->address));
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

    private function remoteSyncCase()
    {
        $this->updateSyncStatus($this->address, Address::SYNC_STATUS_SYNCING, 'started');

        if ($this->address->blockchain_transactions > 30000) {
            //throw new AddressTransactionsLimit('Address ' . $this->address->address . ' has too many transactions. Total: ' . $this->address->blockchain_transactions);
        }

        $this->updateSyncStatus($this->address, Address::SYNC_STATUS_SYNCING, 'remote_sync');

        $syncer = new RemoteFullSyncer($this->address);
        $syncer->sync();

        $this->updateSyncStatus($this->address, Address::SYNC_STATUS_SUCCESS, 'remote_sync');
    }


    private function fullSyncCase()
    {
        // TODO: rename method or even maybe move this code to seperate class
        // TODO: This query returns last row in DB, but for one block address could have several TXs. Possible fix: Take last block number, grab all txs for address in this block, take the latest from litecoin_transactions
        $lastTx = DB::table('litecoin_transactions_addresses')
            ->select(['block_number', 'transaction_hash'])
            ->where('address', $this->address->address)
            ->orderBy('block_number', 'desc')
            ->first();

        $update = [
            'synced_block_number' => $lastTx->block_number,
            'synced_transactions' => DB::raw('(select count(*) as cnt FROM litecoin_transactions_addresses where address = "' . $this->address->address . '")'),
            'last_transaction_hash' => $lastTx->transaction_hash,
            'last_sync_at' => DB::raw('NOW()')
        ];

        Litecoin\Address::where('address', $this->address->address)
            ->update($update);

        $this->updateSyncStatus($this->address, Address::SYNC_STATUS_SUCCESS, 'full_sync');
    }
}
