<?php

namespace App\Services\Litecoin;

use App\Models\Blockchain\Litecoin\Address;
use App\Services\DataServices\Blockchain\RemoteApp\RemoteAPI;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class AddressSummary
{
    private Address $address;
    private BlockchainLitecoin $blockchain;

    public function __construct(Address $address)
    {
        $this->address = $address;
        $this->blockchain = new BlockchainLitecoin();
    }

    public function handle()
    {
        if($this->blockchain->getSyncMode() === BlockchainLitecoin::SYNC_MODE_FULL) {
            $this->fullMode();
        } else {
            $this->remoteMode();
        }

        return $this->address;
    }

    private function fullMode()
    {
        $data = \DB::table('litecoin_transactions_addresses')
            ->select([
                \DB::raw('COUNT(transaction_hash) as total_transactions'),
                \DB::raw('MIN(block_number) as first_block'),
                \DB::raw('MAX(block_number) as last_block')
            ])
            ->where('address', $this->address->address)
            ->first();

        $this->address->blockchain_transactions = $data->total_transactions;
        $this->address->blockchain_last_tx_block = $data->last_block;
        $this->address->blockchain_first_tx_block = $data->first_block;
        $this->address->blockchain_data_updated_at = Carbon::now();

        $this->address->save();
    }

    private function remoteMode()
    {
        $remoteAPI = new RemoteAPI('litecoin');

        $data = $remoteAPI->getAddressDetails($this->address->address);

        $this->address->blockchain_transactions = Arr::get($data, 'total_transactions');
        $this->address->blockchain_last_tx_block = Arr::get($data, 'last_block');
        $this->address->blockchain_first_tx_block = Arr::get($data, 'first_block');
        $this->address->blockchain_data_updated_at = Carbon::now();

        $this->address->save();
    }

    private function getSyncMode()
    {
        return config('blockchain.litecoin.sync-mode');
    }
}
