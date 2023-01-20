<?php

namespace App\Services\Litecoin\Syncers\Address;

use App\Models\Blockchain\Litecoin;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class Full extends Base
{
    public function sync()
    {
        do {
            DB::beginTransaction();
            $break = $this->syncStep();
            DB::commit();

            $this->address->refresh();

            dump(sprintf(
                'Last synced block: %s. First synced block: %s. Last TX: %s. Total TXs: %s.',
                $this->address->synced_block_number,
                $this->address->synced_first_block_number,
                $this->address->last_transaction_hash,
                $this->address->synced_transactions
            ));

            //$this->address->synced_first_block_number === $this->address->blockchain_first_tx_block;
        } while (!$break);
    }

    private function syncStep()
    {
        dump('Full sync step');
        $perPage = 50;

        $beforeBlock = $this->address->synced_first_block_number;
        $list = $this->getList($this->address->address, $beforeBlock > 0 ? $beforeBlock + 1 : $beforeBlock, null, $perPage);

        $maxBlockNumber = null;
        $minBlockNumber = null;
        $savedTransactions = 0;
        foreach ($list as $txData) {
            dump((int)$txData['block_height']);
            $maxBlockNumber = (int)$txData['block_height'] > $maxBlockNumber ? (int)$txData['block_height'] : $maxBlockNumber;
            $minBlockNumber = !isset($minBlockNumber) || (int)$txData['block_height'] < $minBlockNumber ? (int)$txData['block_height'] : $minBlockNumber;

            try {
                $this->saveTx($txData);
                $savedTransactions++;
            } catch (QueryException $e) {
                if ($e->errorInfo[1] === 1062) {
                    dump('Tx has duplicated: ' . $txData['hash']);
                    continue;
                }

                throw $e;
            }
        }

        $firstBlockReached = $minBlockNumber === $this->address->synced_first_block_number || $minBlockNumber === $this->address->blockchain_first_tx_block;

        $update = [
            'synced_block_number' => DB::raw('IF(synced_block_number IS NULL OR ' . $maxBlockNumber . ' > synced_block_number, ' . $maxBlockNumber . ', synced_block_number)'),
            'synced_first_block_number' => $minBlockNumber,
            //'last_transaction_hash' => $lastTransactionHash,
            'synced_transactions' => DB::raw('synced_transactions + ' . $savedTransactions),
            'last_sync_at' => DB::raw('NOW()')
        ];

        if ($firstBlockReached && empty($this->address->blockchain_first_tx_block)) {
            $update['blockchain_first_tx_block'] = $minBlockNumber;
        }

        Litecoin\Address::where('address', $this->address->address)
            ->update($update);

        return $firstBlockReached;
    }

}
