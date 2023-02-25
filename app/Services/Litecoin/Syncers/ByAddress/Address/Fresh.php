<?php

namespace App\Services\Litecoin\Syncers\Address;

use App\Models\Blockchain\Litecoin;
use App\Services\DataServices\Blockchain\BlockCypher\Transaction;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class Fresh extends Base
{
    protected $latestBlockNumber = null;
    protected $oldestBlockNumber = null;

    public function sync()
    {
        do {
            DB::beginTransaction();
            $break = $this->syncStep();
            DB::commit();
        } while (!$break);

        if (isset($this->latestBlockNumber)) {
            Litecoin\Address::where('address', $this->address->address)
                ->update([
                    'synced_block_number' => $this->latestBlockNumber
                ]);
        }
    }

    private function syncStep()
    {
        dump('Fresh sync step');

        $afterBlock = $this->address->synced_block_number;
        $list = $this->getList($this->address->address, $this->oldestBlockNumber, $afterBlock, 5);
        $maxBlockNumber = max(Arr::pluck($list, 'block_height'));
        if ($maxBlockNumber === $this->address->synced_block_number) {
            return true;
        }

        $minBlockNumber = null;
        $savedTransactions = 0;

        foreach ($list as $txData) {
            if(empty(Arr::get($txData, 'block_hash'))) {
                continue;
            }
            $minBlockNumber = !isset($minBlockNumber) || (int)$txData['block_height'] < $minBlockNumber ? (int)$txData['block_height'] : $minBlockNumber;
            $this->latestBlockNumber = (int)$txData['block_height'] > $this->latestBlockNumber ? (int)$txData['block_height'] : $this->latestBlockNumber;

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

        Litecoin\Address::where('address', $this->address->address)
            ->update([
                'synced_transactions' => DB::raw('synced_transactions + ' . $savedTransactions),
                'last_sync_at' => DB::raw('NOW()')
            ]);

        $this->oldestBlockNumber = $minBlockNumber;

        return $minBlockNumber <= $this->address->synced_block_number;
    }
}
