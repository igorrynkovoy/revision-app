<?php

namespace App\Services\Sync\DepthSync;

use App\Models\Blockchain\DepthSync;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

// TODO: Херовое имя класса. Если методы будут дополняться, то надо вынести в общий класс
class AddressList
{
    public function getOneHopAddresses(DepthSync $parentDepthSync)
    {
        $r = DB::select('SELECT
                                  distinct(lta2.address)
                                FROM
                                  litecoin_transactions_addresses as lta
                                  left join litecoin_transactions_addresses as lta2 ON lta.`transaction_hash` = lta2.`transaction_hash`
                                WHERE
                                  lta.`address` = ?
                                HAVING
                                  address not in (
                                    select
                                      address
                                    from
                                      depth_syncs
                                    where
                                      root_sync_id = ?
                                      or id = ?
                                  );', [$parentDepthSync->addressModel->address, $parentDepthSync->root_sync_id ?? $parentDepthSync->id, $parentDepthSync->root_sync_id ?? $parentDepthSync->id]);

        return new Collection(Arr::pluck($r, 'address'));
    }

    public function getRecipientsAddresses(DepthSync $parentDepthSync)
    {
        $r = DB::select('SELECT
                                  distinct(lto2.address)
                                FROM
                                  litecoin_transaction_outputs as lto
                                  left join litecoin_transaction_outputs as lto2 ON lto.`input_transaction_hash` = lto2.`transaction_hash`
                                WHERE
                                  lto.`address` = ?
                                HAVING
                                  address not in (
                                    select
                                      address
                                    from
                                      depth_syncs
                                    where
                                      root_sync_id = ?
                                      or id = ?
                                  );', [$parentDepthSync->addressModel->address, $parentDepthSync->root_sync_id ?? $parentDepthSync->id, $parentDepthSync->root_sync_id ?? $parentDepthSync->id]);

        return new Collection(Arr::pluck($r, 'address'));
    }

    public function getSendersAddresses(DepthSync $parentDepthSync)
    {
        throw new \RuntimeException('NOPE');
        $r = DB::select('SELECT
                                  distinct(lta2.address)
                                FROM
                                  litecoin_transaction_outputs as lto
                                  left join litecoin_transaction_outputs as lto2 ON lto.`input_transaction_hash` = lto2.`transaction_hash`
                                WHERE
                                  lto.`address` = ?
                                HAVING
                                  address not in (
                                    select
                                      address
                                    from
                                      depth_syncs
                                    where
                                      root_sync_id = ?
                                      or id = ?
                                  );', [$parentDepthSync->addressModel->address, $parentDepthSync->root_sync_id ?? $parentDepthSync->id, $parentDepthSync->root_sync_id ?? $parentDepthSync->id]);

        return new Collection(Arr::pluck($r, 'address'));
    }

}
