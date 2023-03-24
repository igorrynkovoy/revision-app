<?php

namespace App\Services\Litecoin;

use App\Services\DataServices\Blockchain\RemoteApp\RemoteAPI;

class BlockchainLitecoin
{
    const LITECOIN = 'litecoin';
    const SYNC_MODE_FULL = 'full';
    const SYNC_MODE_REMOTE = 'remote';

    private string $syncMode;
    private RemoteAPI $remoteAPI;

    public function __construct()
    {
        $this->syncMode = config('blockchain.litecoin.sync-mode');
    }

    public function getRemoteAPI(): ?RemoteAPI
    {
        if ($this->getSyncMode() === self::SYNC_MODE_FULL) {
            return null;
        }

        if (!isset($this->remoteAPI)) {
            $this->remoteAPI = new RemoteAPI(self::LITECOIN);
        }

        return $this->remoteAPI;
    }

    public function getSyncMode()
    {
        return $this->syncMode;
    }
}
