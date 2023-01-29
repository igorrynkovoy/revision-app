<?php

namespace App\Services\Litecoin\Syncers;

use App\Models\Blockchain\Litecoin\Transaction;
use App\Models\Blockchain\Litecoin\TransactionOutput;
use BitWasp\Bitcoin\Address\PayToPubKeyHashAddress;
use BitWasp\Bitcoin\Crypto\EcAdapter\Impl\PhpEcc\Key\PublicKey;
use BitWasp\Bitcoin\Key\Factory\PublicKeyFactory;
use BitWasp\Bitcoin\Network\NetworkFactory;
use BitWasp\Bitcoin\Script\Classifier\OutputClassifier;
use BitWasp\Bitcoin\Script\ScriptFactory;
use BitWasp\Bitcoin\Script\ScriptType;
use Carbon\Carbon;
use Denpa\Bitcoin\Client;
use Denpa\Bitcoin\Exceptions\BadRemoteCallException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FullSync
{
    private TransactionAddressesSync $transactionAddressSync;

    public function __construct()
    {
        $this->client = new Client(config('services.litecoin-wallet.host'));
        $this->transactionAddressSync = new TransactionAddressesSync();
    }

    public function isBlockSynced(int $blockNumber): bool
    {
        return Transaction::query()->where('block_number', $blockNumber)->exists();
    }


    public function getBlockToSync($blockNumber = null)
    {
        $blockNumber = $blockNumber ?? (int)Transaction::query()->max('block_number') + 1;

        try {
            $blockHash = $this->client->getblockhash((int)$blockNumber)->result();
        } catch (BadRemoteCallException $exception) {
            if ($exception->getCode() === -8) {
                return null;
            }
        }

        return $this->client->getblock($blockHash, 2)->result();
    }

    public function handleBlock($block)
    {
        $txs = Arr::get($block, 'tx');
        $blockHeight = Arr::get($block, 'height');
        $blockTime = Arr::get($block, 'time');

        DB::beginTransaction();
        try {
            foreach ($txs as $tx) {
                $this->saveTx($tx, $blockHeight, $blockTime);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        // TODO: Sync only when normal sync, when syncing only latest blocks
        //$this->transactionAddressSync->syncBlock($blockHeight);

        DB::commit();
    }

    private function saveTx($tx, $blockHeight, $blockTime)
    {
        $txid = $tx['txid'];
        $vins = Arr::get($tx, 'vin');
        $vouts = Arr::get($tx, 'vout');

        $totalAmount = '0';
        $outputs = [];
        foreach ($vouts as $vout) {
            $addresses = Arr::get($vout, 'scriptPubKey.addresses', []);

            if (empty($addresses) && $vout['scriptPubKey']['type'] === 'pubkey') {
                $addresses = $this->decodeScriptPubKey($vout['scriptPubKey']);
            }

            if (count($addresses) !== 1) {
                Log::error('Transaction ' . $txid . ' has invalid output addresses in output ' . $vout['n'], $vout);
                $addresses = ['XXXXXXXXXXXXXXXX'];
            }
            $amount = bcmul(number_format($vout['value'], 8, '.', ''), 10 ** 8);
            $totalAmount = bcadd($totalAmount, $amount);
            $outputs[] = [
                'address' => $addresses[0],
                'transaction_hash' => $txid,
                'block_number' => $blockHeight,
                'index' => $vout['n'],
                'value' => $amount,
                'script_type' => Arr::get($vout, 'scriptPubKey.type')
            ];
        }

        TransactionOutput::query()
            ->getQuery()
            ->insertOrIgnore($outputs);
        $isCoinbase = false;
        $vinToPool = [];
        foreach ($vins as $index => $vin) {
            if (!empty(Arr::get($vin, 'coinbase'))) {
                $isCoinbase = true;
                continue;
            }

            $updated = TransactionOutput::query()->getQuery()
                ->where('transaction_hash', $vin['txid'])
                ->where('index', $vin['vout'])
                ->update([
                    'input_transaction_hash' => $txid,
                    'input_index' => $index
                ]);

            if (!$updated) {
                $vinToPool[] = [
                    'input_transaction_hash' => $txid,
                    'input_index' => $index,
                    'output_transaction_hash' => $vin['txid'],
                    'output_index' => $vin['vout'],
                    'input_block_number' => $blockHeight
                ];
            }
        }

        DB::table('litecoin_transactions_inputs_pool')
            ->insert($vinToPool);

        Transaction::query()->getQuery()
            ->insert([
                'hash' => $txid,
                'block_number' => $blockHeight,
                'is_coinbase' => $isCoinbase,
                'total_inputs' => count(Arr::get($tx, 'vin', [])),
                'total_outputs' => count(Arr::get($tx, 'vout', [])),
                'fee' => 0, // TODO
                'amount' => $totalAmount,
                'processed' => false,
                'created_at' => Carbon::createFromTimestampUTC($blockTime)
            ]);
    }

    private function decodeScriptPubKey($data)
    {
        $hex = $data['hex'];
        $script = ScriptFactory::fromHex($hex);
        $oc = new OutputClassifier();
        $d = $oc->decode($script);
        if ($d->getType() !== ScriptType::P2PK) {
            throw new \RuntimeException('Cannot obtain address');
        }

        $pubKey = $d->getSolution();
        $f = new PublicKeyFactory();
        /** @var PublicKey $k */
        $k = $f->fromBuffer($pubKey);
        $p2pkh = new PayToPubKeyHashAddress($k->getPubKeyHash());

        return [$p2pkh->getAddress(NetworkFactory::litecoin())];
    }
}

