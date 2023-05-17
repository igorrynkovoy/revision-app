<?php

namespace App\Repositories\Blockchain\Litecoin;

use App\Jobs\Blockchain\Litecoin\SyncAddress;
use App\Models\Blockchain\Litecoin\Address;
use App\Repositories\Interfaces\Blockchain\Litecoin\AddressRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AddressRepository implements AddressRepositoryInterface
{
    public function getAddressByAddress($string): Address
    {
        /** @var Address $address */
        $address = Address::firstOrCreate(['address' => $string]);

        return $address->wasRecentlyCreated ? $address->fresh() : $address;
    }

    public function getAddresses($list): Collection
    {
        $addresses = Address::query()->whereIn('address', $list)->get();

        $missed = array_diff($list, $addresses->pluck('address')->toArray());
        foreach ($missed as $missedAddress) {
            Address::query()->insert(['address' => $missedAddress, 'created_at' => \DB::raw('NOW()'), 'updated_at' => \DB::raw('NOW()')]);
        }

        return $addresses->merge(Address::query()->whereIn('address', $missed)->get());
    }

    public function getNeighborsAddresses($address): Collection
    {
        $list = DB::select('SELECT
                                  DISTINCT(lta2.address)
                                FROM
                                  litecoin_transactions_addresses as lta
                                  LEFT JOIN litecoin_transactions_addresses as lta2
                                      ON lta.`transaction_hash` = lta2.`transaction_hash`
                                WHERE
                                  lta.`address` = ?;', [$address]);

        return $this->getAddresses(Arr::pluck($list, 'address'));
    }

    public function toolGetSendersToAddress($address): array
    {
        $list = DB::select('SELECT lto2.address, COUNT(DISTINCT(lto2.transaction_hash)) as tx_count
                                   FROM litecoin_transaction_outputs as lto1
                                   LEFT JOIN litecoin_transaction_outputs as lto2 ON lto1.`transaction_hash` = lto2.`input_transaction_hash`
                                   WHERE lto1.address = ? GROUP BY lto2.address ORDER BY tx_count DESC;', [$address]);

        return $list;
    }

    public function toolGetRecipientsByAddress($address): array
    {
        $list = DB::select('SELECT lto2.address, COUNT(DISTINCT(lto2.transaction_hash)) as tx_count
                                   FROM litecoin_transaction_outputs as lto1
                                   LEFT JOIN litecoin_transaction_outputs as lto2 ON lto1.`input_transaction_hash` = lto2.`transaction_hash`
                                   WHERE lto1.address = ? GROUP BY lto2.address ORDER BY tx_count DESC;', [$address]);

        return $list;
    }

}
