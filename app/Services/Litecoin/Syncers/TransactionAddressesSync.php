<?php

namespace App\Services\Litecoin\Syncers;

use Illuminate\Support\Facades\DB;

class TransactionAddressesSync
{
    public function getLastSyncedBlockNumber(): int
    {
        return (int)DB::table('litecoin_transactions_addresses')
            ->max('block_number');
    }

    public function getMaximumBlockNumber(): int
    {
        return (int)DB::table('litecoin_transactions')
            ->max('block_number');
    }

    public function syncBlock($blockNumber, $chunk = 1)
    {
        $toBlock = $blockNumber + $chunk - 1;
        DB::insert('INSERT IGNORE INTO litecoin_transactions_addresses
                select CONCAT(lto.address, lt.hash) as id, lto.address, lt.hash, lt.block_number from litecoin_transactions as lt
                left join litecoin_transaction_outputs as lto on lt.hash = lto.`transaction_hash`
                where lt.block_number BETWEEN ? AND ?
                union
                select lto2.address, lt.hash, lt.block_number from litecoin_transactions as lt
                left join litecoin_transaction_outputs as lto2 on lt.hash = lto2.`input_transaction_hash`
                where lt.block_number BETWEEN ? AND ? having address is not null;', [$blockNumber, $toBlock, $blockNumber, $toBlock]);
    }
}
