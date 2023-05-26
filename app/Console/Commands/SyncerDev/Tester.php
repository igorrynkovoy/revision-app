<?php

namespace App\Console\Commands\SyncerDev;

use App\Services\DataServices\Blockchain\BlockCypher;
use BitWasp\Bitcoin\Address\AddressCreator;
use BitWasp\Bitcoin\Address\PayToPubKeyHashAddress;
use BitWasp\Bitcoin\Crypto\EcAdapter\Impl\PhpEcc\Key\PublicKey;
use BitWasp\Bitcoin\Key\Factory\PublicKeyFactory;
use BitWasp\Bitcoin\Network\Network;
use BitWasp\Bitcoin\Network\NetworkFactory;
use BitWasp\Bitcoin\Script\Classifier\OutputClassifier;
use BitWasp\Bitcoin\Script\Opcodes;
use BitWasp\Bitcoin\Script\P2shScript;
use BitWasp\Bitcoin\Script\ScriptFactory;
use BitWasp\Bitcoin\Script\ScriptInfo\PayToPubkey;
use BitWasp\Bitcoin\Script\ScriptInfo\PayToPubkeyHash;
use BitWasp\Bitcoin\Script\ScriptType;
use BitWasp\Bitcoin\Transaction\TransactionFactory;
use Denpa\Bitcoin\Client;
use Illuminate\Console\Command;

class Tester extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'syncer:tester';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->testA();
    }

    public function testA()
    {
        $txid = 'abb72328107e2f3af52c746069a99b4b3b908071d6a7e833df5bfdc997ac01d1';

        $client = new Client(config('services.litecoin-wallet.host'));
        $txData = $client->getrawtransaction($txid, true)->result();
        $txData2 = $client->decoderawtransaction('0200000000080238518d222c4cc33d4547bee2bcb3132246ff8ea8c9492138898ae829067745010000000000ffffffff765b8c38a193188304be737dd0131c43f7ed3c2bc43b4fbe55255af7be50abe30000000000ffffffff019b9a49f11001000022582011350c09bbf7c03e7a58a8a72fefd65f72a4b20be2bfcf5d434c0f592bdb15290000000000');
        dd($txData, $txData2);
        $k = $this->decodeScriptPubKey($txData);
        dd($k);
    }

    private function decodeScriptPubKey($data)
    {
        $hex = $data['hex'];
        $f = new PublicKeyFactory();
        dd($f->fromHex('11350c09bbf7c03e7a58a8a72fefd65f72a4b20be2bfcf5d434c0f592bdb1529'));

        $script = ScriptFactory::fromHex($hex);
        $oc = new OutputClassifier();
        $d = $oc->decode($script);
        if ($d->getType() !== ScriptType::P2PK) {
            throw new \RuntimeException('Cannot obtain address');
        }

        $pubKey = $d->getSolution();
        /** @var PublicKey $k */
        $k = $f->fromBuffer($pubKey);

        $p2pkh = new PayToPubKeyHashAddress($k->getPubKeyHash());

        return [$p2pkh->getAddress(NetworkFactory::litecoin())];
    }


    public function testT()
    {
        $hex = '04284464458f95a72e610ecd7a561e8c2bdb46c491b347e4a375aa8f2e3b3ed56e99552e789265b6e52a2fc9a00edcdd6c032979dd81a7f1201b62427076768a7a';
        $script = ScriptFactory::fromHex('4104284464458f95a72e610ecd7a561e8c2bdb46c491b347e4a375aa8f2e3b3ed56e99552e789265b6e52a2fc9a00edcdd6c032979dd81a7f1201b62427076768a7aac');
        $ac = new AddressCreator();
        dd($ac->fromOutputScript($script));
        $oc = new OutputClassifier();
        $d = $oc->decode($script);
        $pubKey = $d->getSolution();
        $f = new PublicKeyFactory();
        /** @var PublicKey $k */
        $k = $f->fromBuffer($pubKey);

        $p2pkh = new PayToPubKeyHashAddress($k->getPubKeyHash());

        dd($p2pkh->getAddress(NetworkFactory::litecoin()));
        $client = new Client(config('services.litecoin-wallet.host'));
        $blockCypher = new BlockCypher('ltc');
        $blockHash = $client->getblockhash(2199994)->result();
        //$data = $client->getblock($blockHash, 2)->result();
        $txId = 'c4c82f5b932c3effebd6c898004b48c6d7e89682f0f95db978e15222b364334a';
        //$apiData = $blockCypher->getTransaction($txId);
        $txData = $client->getrawtransaction($txId, true)->result();
        //dump($apiData);
        dump($txData);
        // 0 160014e7f3d98178d7e38f9d82b36d7c35a724e78ef82e
        $rt = TransactionFactory::fromHex($txData['hex']);
        $input = $rt->getInputs()[0];

        $p2sh = new P2shScript($input->getScript());
        dd($p2sh->getScriptHash()->getHex());
        dd($p2sh->getAddress()->getAddress(NetworkFactory::litecoin()));


    }
}
